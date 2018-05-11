<?php
namespace Formapro\Pvm\Enqueue;

use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Consumption\QueueSubscriberInterface;
use Formapro\Pvm\NullTokenLocker;
use Formapro\Pvm\PessimisticLockException;
use Formapro\Pvm\TokenLockerInterface;
use Formapro\Pvm\TokenException;
use Formapro\Pvm\ProcessEngine;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProcessor;
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
     * @var TokenLockerInterface
     */
    private $tokenLocker;

    public function __construct(ProcessEngine $processEngine, TokenLockerInterface $tokenLocker = null, LoggerInterface $logger = null)
    {
        $this->processEngine = $processEngine;
        $this->tokenLocker = $tokenLocker ?: new NullTokenLocker();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $psrMessage, PsrContext $psrContext): HandleAsyncTransitionResult
    {
        try {
            $message = HandleAsyncTransition::jsonUnserialize($psrMessage->getBody());
        } catch (\Throwable $e) {
            return HandleAsyncTransitionResult::reject($e->getMessage());
        }

        try {
            $token = $this->processEngine->getToken($message->getToken());

            if ($token->getCurrentTransition()->getId() !== $message->getTokenTransitionId()) {
                return HandleAsyncTransitionResult::reject('Token has changed the current transition.');
            }
        } catch (TokenException $e) {
            return HandleAsyncTransitionResult::reject('Token has not been found.');
        }

        try {
            $this->tokenLocker->lock($message->getToken(), false);

            $token = $this->processEngine->getToken($message->getToken());

            if ($token->getCurrentTransition()->getId() !== $message->getTokenTransitionId()) {
                return HandleAsyncTransitionResult::reject('Token has changed the current transition.');
            }

            $waitTokens = $this->processEngine->proceed($token, $this->logger);

            $result = HandleAsyncTransitionResult::ack();
            $result->setWaitTokens($waitTokens);

            return $result;
        } catch (PessimisticLockException $e) {
            return HandleAsyncTransitionResult::requeue('Token is locked at the moment. Requeue it to process later.');
        } catch (TokenException $e) {
            return HandleAsyncTransitionResult::reject('Token has not been found.');
        } finally {
            $this->tokenLocker->unlock($message->getToken());
        }
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
