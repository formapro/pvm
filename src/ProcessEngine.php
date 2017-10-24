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
        $tokenTransition = $token->getCurrentTransition();
        $currentTransition = $tokenTransition->getTransition();

        try {


            if (false == $node = $currentTransition->getTo()) {
                throw new \LogicException(sprintf(
                    'Out node is missing. process: %s, transitions: %s',
                    $token->getProcess()->getId(),
                    $tokenTransition->getId()
                ));
            }

            $this->log('On transition: %s -> %s',
                $currentTransition->getFrom() ? $currentTransition->getFrom()->getLabel() : 'start',
                $currentTransition->getTo() ? $currentTransition->getTo()->getLabel() : 'end'
            );

            $behavior = $this->behaviorRegistry->get($node->getBehavior());

            if ($tokenTransition->isWaiting()) {
                if (false === $behavior instanceof SignalBehavior) {
                    throw new \LogicException(sprintf('Expected SignalBehavior'));
                }

                $this->log('Signal behavior: %s', $node->getBehavior());
                $behaviorResult = $behavior->signal($token);
            } else {
                $this->log('Execute behavior: %s', $node->getBehavior());
                $behaviorResult = $behavior->execute($token);
            }

            $tokenTransition->setPassed();

            if (false == $behaviorResult) {
                $tmpTransitions = [];
                foreach ($token->getProcess()->getOutTransitions($node) as $transition) {
                    if (empty($transition->getName())) {
                        $tmpTransitions[] = $transition;
                    }
                }
            } elseif (is_string($behaviorResult)) {
                $tmpTransitions = $token->getProcess()->getOutTransitionsWithName($node, $behaviorResult);
                if (empty($tmpTransitions)) {
                    throw new \LogicException(sprintf('The transitions with the name %s could not be found', $behaviorResult));
                }
            } elseif ($behaviorResult instanceof Transition) {
                $tmpTransitions = [$behaviorResult];
            } elseif (is_array($behaviorResult)) {
                $tmpTransitions = [];
                foreach ($behaviorResult as $transition) {
                    if (is_string($transition)) {
                        $transitionsWithName = $token->getProcess()->getOutTransitionsWithName($node, $transition);
                        if (empty($transitionsWithName)) {
                            throw new \LogicException(sprintf('The transitions with the name %s could not be found', $transition));
                        }

                        $tmpTransitions = array_merge($tmpTransitions, $transitionsWithName);
                    } elseif ($transition instanceof Transition) {
                        $tmpTransitions[] = $tmpTransitions;
                    } else {
                        throw new \LogicException('Unsupported element of array. Could be either instance of Transition or its name (string).');
                    }
                }
            } else {
                throw new \LogicException('Unsupported behavior result. Could be either instance of Transition, an array of Transitions, null or transition name (string).');
            }

            $transitions = [];
            foreach ($tmpTransitions as $transition) {
                $transitions[] = $transition;
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
                    $token->addTransition(TokenTransition::createFor($transition, $tokenTransition->getWeight()));
                    $this->transition($token);
                } else {
                    $newToken = $token->getProcess()->createToken($transition);
                    $newToken->getCurrentTransition()->setWeight($tokenTransition->getWeight());

                    $this->transition($newToken);
                }
            }
        } catch (InterruptExecutionException $e) {
            $tokenTransition->setInterrupted();

            return;
        } catch (WaitExecutionException $e) {
            $tokenTransition->setWaiting();
            $this->waitTokens[] = $token;

            return;
        }
    }

    private function transition(Token $token)
    {
        $transition = $token->getCurrentTransition()->getTransition();

        if (false == $transition->isActive()) {
            $token->getCurrentTransition()->setInterrupted();

            return;
        }

        if ($transition->isAsync()) {
            $this->asyncTokens[] = $token;

            return;
        }

        $this->doProceed($token);
    }
}
