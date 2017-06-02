<?php
namespace Formapro\Pvm;

use Formapro\Pvm\Exception\InterruptExecutionException;
use Formapro\Pvm\Exception\WaitExecutionException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ProcessEngine
{
    /**
     * @var BehaviorRegistry
     */
    private $behaviorRegistry;

    /**
     * @var ProcessStorage
     */
    private $processExecutionStorage;

    /**
     * @var AsyncTransition
     */
    private $asyncTransition;

    /**
     * @var Transition[]
     */
    private $asyncTokens;

    /**
     * @var Token[]
     */
    private $waitTokens;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param BehaviorRegistry $behaviorRegistry
     * @param ProcessStorage $processExecutionStorage
     * @param AsyncTransition $asyncTransition
     */
    public function __construct(
        BehaviorRegistry $behaviorRegistry,
        ProcessStorage $processExecutionStorage = null,
        AsyncTransition $asyncTransition = null
    ) {
        $this->behaviorRegistry = $behaviorRegistry;
        $this->processExecutionStorage = $processExecutionStorage ?: new NullProcessStorage();
        $this->asyncTransition = $asyncTransition ?: new AsyncTransitionIsNotConfigured();
        $this->asyncTokens = [];
        $this->waitTokens = [];
    }

    private function log($text, ...$args)
    {
        $this->logger->debug(sprintf('[ProcessEngine] '.$text, ...$args));
    }

    /**
     * @param Token $token
     * @param LoggerInterface $logger
     *
     * @return Token[]
     */
    public function proceed(Token $token, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();

        try {
            $this->log('Start execution: process: %s, token: %s', $token->getProcess()->getId(), $token->getId());
            $this->doProceed($token);
            $this->processExecutionStorage->persist($token->getProcess());

            if ($this->asyncTokens) {
                $this->asyncTransition->transition($this->asyncTokens);
            }

            return $this->waitTokens;
        } catch (\Exception $e) {
            // handle error
            throw $e;
        } catch (\Error $e) {
            // handle error
            throw $e;
        } finally {
            $this->asyncTokens = [];
            $this->waitTokens = [];
            $this->logger = null;
        }
    }

    private function doProceed(Token $token)
    {
        try {
            if (false == $node = $token->getTransition()->getTo()) {
                throw new \LogicException(sprintf(
                    'Out node is missing. process: %s, transitions: %s',
                    $token->getProcess()->getId(),
                    $token->getTransition()->getId()
                ));
            }

            $this->log('On transition: %s -> %s',
                $token->getTransition()->getFrom() ? $token->getTransition()->getFrom()->getLabel() : 'start',
                $token->getTransition()->getTo() ? $token->getTransition()->getTo()->getLabel() : 'end'
            );

            $behavior = $this->behaviorRegistry->get($node->getBehavior());

            if ($token->getTransition()->isWaiting()) {
                if (false === $behavior instanceof SignalBehavior) {
                    throw new \LogicException(sprintf('Expected SignalBehavior'));
                }

                $this->log('Signal behavior: %s', $node->getBehavior());
                $transitions = $behavior->signal($token);
            } else {
                $this->log('Execute behavior: %s', $node->getBehavior());
                $transitions = $behavior->execute($token);
            }

            $token->getTransition()->setPassed();

            if (false == $transitions) {
                $tmpTransitions = $token->getProcess()->getOutTransitions($node);

                $transitions = [];
                foreach ($tmpTransitions as $transition) {
                    if (empty($transition->getName())) {
                        $transition->setWeight($token->getTransition()->getWeight());

                        $transitions[] = $transition;
                    }

                }
            }

            $tmpTransitions = $transitions;
            $transitions = [];
            foreach ($tmpTransitions as $transition) {
                if (is_string($transition)) {
                    $transitions = array_merge($transitions, $token->getProcess()->getOutTransitionsWithName($node, $transition));
                } else {
                    $transitions[] = $transition;
                }
            }

            if (false == $transitions) {
                $this->log('End execution');
                return;
            }

            $first = true;
            foreach ($transitions as $transition) {
                $this->log('Next transition: %s -> %s',
                    $transition->getFrom() ? $transition->getFrom()->getLabel() : 'start',
                    $transition->getTo() ? $transition->getTo()->getLabel() : 'end'
                );

                if ($first) {
                    $first = false;
                    $token->setTransition($transition);
                    $this->transition($token);
                } else {
                    $this->transition($token->getProcess()->createToken($transition));
                }
            }
        } catch (InterruptExecutionException $e) {
            $token->getTransition()->setInterrupted();

            return;
        } catch (WaitExecutionException $e) {
            $token->getTransition()->setWaiting();
            $this->waitTokens[] = $token;

            return;
        }
    }

    private function transition(Token $token)
    {
        $transition = $token->getTransition();

        if (false == $transition->isActive()) {
            $token->getTransition()->setInterrupted();

            return;
        }

        if ($transition->isAsync()) {
            $this->asyncTokens[] = $token;

            return;
        }

        $this->doProceed($token);
    }
}
