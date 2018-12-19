<?php
namespace Formapro\Pvm\Enqueue;

use Formapro\Pvm\NullTokenLocker;
use Formapro\Pvm\PessimisticLockException;
use Formapro\Pvm\TokenLockerInterface;
use Formapro\Pvm\TokenException;
use Formapro\Pvm\ProcessEngine;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HandleAsyncTransitionProcessor implements Processor
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

    public function process(Message $interopMessage, Context $interopContext): HandleAsyncTransitionResult
    {
        try {
            $message = HandleAsyncTransition::jsonUnserialize($interopMessage->getBody());
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
}
