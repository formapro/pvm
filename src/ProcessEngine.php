<?php
namespace Formapro\Pvm;

use Formapro\Pvm\Exception\InterruptExecutionException;
use Psr\Log\LoggerInterface;

class ProcessEngine
{
    /**
     * @var BehaviorRegistry
     */
    private $behaviorRegistry;

    /**
     * @var ProcessStorage
     */
    private $processStorage;

    /**
     * @var AsyncTransition
     */
    private $asyncTransition;

    /**
     * @var Transition[]
     */
    private $asyncTokens;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param BehaviorRegistry $behaviorRegistry
     * @param ProcessStorage   $processStorage
     */
    public function __construct(BehaviorRegistry $behaviorRegistry, ProcessStorage $processStorage)
    {
        $this->behaviorRegistry = $behaviorRegistry;
        $this->processStorage = $processStorage;
    }

    private function log($text, ...$args)
    {
        $this->logger->debug(sprintf('[ProcessEngine] '.$text, ...$args));
    }

    public function proceed(Token $token, LoggerInterface $logger)
    {
        $this->logger = $logger;

        try {
            $this->log('Start execution: process: %s, token: %s', $token->getProcess()->getId(), $token->getId());
            $this->doProceed($token);
            $this->processStorage->persist($token->getProcess());
            $this->asyncTransition->transition($this->asyncTokens);
        } catch (\Exception $e) {
            // handle error
        } catch (\Error $e) {
            // handle error
        } finally {
            $this->asyncTokens = [];
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
                $token->getTransition()->getFrom() ? $token->getTransition()->getFrom()->getId() : 'start',
                $token->getTransition()->getTo() ? $token->getTransition()->getTo()->getId() : 'end'
            );

            $behavior = $this->behaviorRegistry->get($node->getBehavior());

            $this->log('Execute behavior: %s', $node->getBehavior());

            $transitions = $behavior->execute($token);
            $token->getTransition()->setPassed();

            if (false == $transitions) {
                $transitions = $token->getProcess()->getOutTransitions($node);

                foreach ($transitions as $transition) {
                    $transition->setWeight($token->getTransition()->getWeight());
                }
            }

            if (false == $transitions) {
                $this->log('End execution');
                return;
            }

            $first = true;
            foreach ($transitions as $transition) {
                $this->log('Next transition: %s -> %s',
                    $transition->getFrom() ? $transition->getFrom()->getId() : 'start',
                    $transition->getTo() ? $transition->getTo()->getId() : 'end'
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
            $token->getTransition()->setPassed();

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
