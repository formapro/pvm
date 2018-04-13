<?php
namespace Formapro\Pvm\Enqueue;

use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Consumption\QueueSubscriberInterface;
use Enqueue\Consumption\Result;
use Formapro\Pvm\NullTokenLocker;
use Formapro\Pvm\PessimisticLockException;
use Formapro\Pvm\TokenContext;
use Formapro\Pvm\TokenLockerInterface;
use Formapro\Pvm\Yadm\TokenException;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProcessor;
use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessEngine;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TokenContext
     */
    private $tokenContext;

    /**
     * @var TokenLockerInterface
     */
    private $tokenLocker;

    public function __construct(ProcessEngine $processEngine, TokenContext $tokenContext, TokenLockerInterface $tokenLocker = null, LoggerInterface $logger = null)
    {
        $this->processEngine = $processEngine;
        $this->tokenContext = $tokenContext;
        $this->tokenLocker = $tokenLocker ?: new NullTokenLocker();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $psrMessage, PsrContext $psrContext)
    {
        try {
            $message = HandleAsyncTransition::jsonUnserialize($psrMessage->getBody());
        } catch (\Throwable $e) {
            return Result::reject($e->getMessage());
        }

        try {
            $token = $this->tokenContext->getToken($message->getToken());

            if ($token->getCurrentTransition()->getId() !== $message->getTokenTransitionId()) {
                return self::REJECT;
            }
        } catch (TokenException $e) {
            return self::REJECT;
        }

        if ($this->tokenLocker->locked($message->getToken())) {
            return self::REQUEUE;
        }

        try {
            $this->tokenLocker->lock($message->getToken());

            $token = $this->tokenContext->getToken($message->getToken());

            if ($token->getCurrentTransition()->getId() !== $message->getTokenTransitionId()) {
                return self::REJECT;
            }

            $this->processEngine->proceed($token, $this->logger);
        } catch (PessimisticLockException $e) {
            return self::REQUEUE;
        } finally {
            $this->tokenLocker->unlock($message->getToken());
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
