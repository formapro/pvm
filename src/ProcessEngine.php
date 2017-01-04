<?php
namespace Formapro\Pvm;

use Formapro\Pvm\Exception\InterruptExecutionException;

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
     * @param BehaviorRegistry $behaviorRegistry
     * @param ProcessStorage   $processStorage
     */
    public function __construct(BehaviorRegistry $behaviorRegistry, ProcessStorage $processStorage)
    {
        $this->behaviorRegistry = $behaviorRegistry;
        $this->processStorage = $processStorage;
    }

    private function log($text)
    {
        echo $text . PHP_EOL;
    }

    public function proceed(Token $token)
    {
        try {
            $this->doProceed($token);
            $this->processStorage->persist($token->getProcess());
            $this->asyncTransition->transition($this->asyncTokens);
        } catch (\Exception $e) {
            // handle error
        } catch (\Error $e) {
            // handle error
        } finally {
            $this->asyncTokens = [];
        }
    }

    private function doProceed(Token $token)
    {
        try {
            $behavior = $this->behaviorRegistry->get($token->getTransition()->getTo()->getBehavior());

            $this->log('execute behavior: ' . get_class($behavior));

            if ($transitions = $behavior->execute($token)) {
                $token->getTransition()->setPassed();

                foreach ($transitions as $transition) {
                    $this->transition($token->getProcess()->createToken($transition));
                }
            } else {
                $transitions = $token->getProcess()->getOutTransitionsForNode($token->getTransition()->getTo());
                if (false == $transitions) {
                    // end
                    return;
                } elseif (1 < count($transitions)) {
                    throw new \LogicException(sprintf('Cant apply default transition for node: %s', $token->getTransition()->getTo()->getId()));
                }

                // ??? copy weight
                $transitions[0]->setWeight($token->getTransition()->getWeight());

                $token->getTransition()->setPassed();

                $token->setTransition($transitions[0]);

                $this->transition($token);
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
