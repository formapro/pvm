<?php
namespace Formapro\Pvm\Enqueue;

use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Consumption\Result;
use Enqueue\Psr\PsrContext;
use Enqueue\Psr\PsrMessage;
use Enqueue\Psr\PsrProcessor;
use Enqueue\Util\JSON;
use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\ProcessStorage;
use Psr\Log\NullLogger;

class HandleAsyncTransitionProcessor implements PsrProcessor, CommandSubscriberInterface
{
    const COMMAND = 'pvm_handle_async_transition';

    const TOPIC = 'pvm_handle_async_transition';

    /**
     * @var ProcessEngine
     */
    private $processEngine;

    /**
     * @var ProcessStorage
     */
    private $processExecutionStorage;

    /**
     * @param ProcessEngine $processEngine
     * @param ProcessStorage $processExecutionStorage
     */
    public function __construct(ProcessEngine $processEngine, ProcessStorage $processExecutionStorage)
    {
        $this->processEngine = $processEngine;
        $this->processExecutionStorage = $processExecutionStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $psrMessage, PsrContext $psrContext)
    {
        if ($psrMessage->isRedelivered()) {
            return Result::reject('The message failed. Remove it');
        }

        $data = JSON::decode($psrMessage->getBody());

        /** @var Process $process */
        if (false == $process = $this->processExecutionStorage->get($data['process'])) {
            return Result::reject('Process was not found');
        }

        if (false == $token = $process->getToken($data['token'])) {
            return Result::reject('No such token');
        }

        try {
            $this->processEngine->proceed($token, new NullLogger());
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
        return static::COMMAND;
    }
}
