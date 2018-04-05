<?php
namespace Formapro\Pvm\Enqueue;

use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Consumption\QueueSubscriberInterface;
use Enqueue\Consumption\Result;
use Enqueue\Util\JSON;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProcessor;
use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\ProcessStorage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HandleAsyncTransitionProcessor implements PsrProcessor, CommandSubscriberInterface, QueueSubscriberInterface
{
    const COMMAND = 'pvm_handle_async_transition';

    /**
     * @var ProcessEngine
     */
    private $processEngine;

    /**
     * @var ProcessStorage
     */
    private $processExecutionStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ProcessEngine $processEngine, ProcessStorage $processExecutionStorage, LoggerInterface $logger = null)
    {
        $this->processEngine = $processEngine;
        $this->processExecutionStorage = $processExecutionStorage;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $psrMessage, PsrContext $psrContext)
    {
        try {
            $data = JSON::decode($psrMessage->getBody());
        } catch (\Exception $e) {
            return Result::reject($e->getMessage());
        }

        if (false == array_key_exists('token', $data)) {
            return Result::reject('Message miss required token field.');
        }

        /** @var Process $process */
        if (false == $process = $this->processExecutionStorage->getByToken($data['token'])) {
            return Result::reject('Process was not found');
        }

        try {
            $token = $this->processEngine->getProcessToken($process, $data['token']);

            $this->processEngine->proceed($token, $this->logger);
        } finally {
            $this->processExecutionStorage->persist($process);
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedCommand()
    {
        return [
            'processorName' => static::COMMAND,
            'queueName' => static::COMMAND,
            'queueNameHardcoded' => true,
            'exclusive' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedQueues()
    {
        return [static::COMMAND];
    }
}
