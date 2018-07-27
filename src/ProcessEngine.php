<?php
namespace Formapro\Pvm;

use Formapro\Pvm\Exception\InterruptExecutionException;
use Formapro\Pvm\Exception\WaitExecutionException;
use function Makasim\Values\set_value;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ProcessEngine implements DAL
{
    /**
     * @var BehaviorRegistry
     */
    private $behaviorRegistry;

    /**
     * @var AsyncTransition
     */
    private $asyncTransition;

    /**
     * @var DAL
     */
    private $dal;

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

    private $logException;

    private $exceptionLogged;

    public function __construct(
        BehaviorRegistry $behaviorRegistry,
        DAL $dal = null,
        AsyncTransition $asyncTransition = null
    ) {
        $this->behaviorRegistry = $behaviorRegistry;
        $this->dal = $dal ?: new InMemoryDAL();
        $this->asyncTransition = $asyncTransition ?: new AsyncTransitionIsNotConfigured();

        $this->asyncTokens = [];
        $this->waitTokens = [];
        $this->logException = false;
        $this->exceptionLogged = false;
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

            if ($this->asyncTokens) {
                $this->log(sprintf('Handle async transitions: %s', count($this->asyncTokens)));

                $this->asyncTransition->transition($this->asyncTokens);
            }

            return $this->waitTokens;
        } finally {
            $this->asyncTokens = [];
            $this->waitTokens = [];
            $this->logger = null;
            $this->exceptionLogged = false;
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

            $token->addTransition(TokenTransition::createForNewState($token, TokenTransition::STATE_PASSED));

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
                        $tmpTransitions[] = $transition;
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
                $this->persistToken($token);

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
                    $newToken = $this->forkProcessToken($token);
                    $newToken->addTransition(TokenTransition::createFor($transition, $transition->getWeight()));
                    $newToken->getCurrentTransition()->setWeight($tokenTransition->getWeight());

                    $this->transition($newToken);
                }
            }

            $this->persistToken($token);
        } catch (InterruptExecutionException $e) {
            $tokenTransition = TokenTransition::createForNewState($token, TokenTransition::STATE_INTERRUPTED);
            $tokenTransition->setReason($e->getMessage());
            $token->addTransition($tokenTransition);

            $this->persistToken($token);

            return;
        } catch (WaitExecutionException $e) {
            if ($e->isCreateWaitingTransition()) {
                $tokenTransition = TokenTransition::createForNewState($token, TokenTransition::STATE_WAITING);
                $tokenTransition->setReason($e->getMessage());

                $token->addTransition($tokenTransition);
            }

            $this->waitTokens[] = $token;

            $this->persistToken($token);

            return;
        } catch (\Throwable $e) {
            if ($this->logException && false == $this->exceptionLogged) {
                set_value($tokenTransition, 'exception', (string) $e);
                $this->persistToken($token);

                $this->exceptionLogged = true;
            }

            throw $e;
        }
    }

    private function transition(Token $token)
    {
        $transition = $token->getCurrentTransition()->getTransition();

        if (false == $transition->isActive()) {
            $token->getCurrentTransition()->setInterrupted();
            $this->dal->persistToken($token);

            return;
        }

        if ($transition->isAsync()) {
            $this->asyncTokens[] = $token;

            $this->persistToken($token);

            return;
        }

        $this->doProceed($token);
    }

    public function createProcessToken(Process $process, string $id = null): Token
    {
        return $this->dal->createProcessToken($process, $id);
    }

    public function createToken(Process $process, string $id = null): Token
    {
        return $this->dal->createProcessToken($process, $id);
    }

    public function createTokenFor(Transition $transition, int $weight = 1, string $id = null): Token
    {
        $token = $this->dal->createProcessToken($transition->getProcess(), $id);
        $token->addTransition(TokenTransition::createFor($transition, $weight));

        return $token;
    }

    public function forkProcessToken(Token $token, string $id = null): Token
    {
        return $this->dal->forkProcessToken($token, $id);
    }

    public function getProcessTokens(Process $process): \Traversable
    {
        return $this->dal->getProcessTokens($process);
    }

    public function getProcessToken(Process $process, string $id): Token
    {
        return $this->dal->getProcessToken($process, $id);
    }

    public function persistToken(Token $token): void
    {
        $this->dal->persistToken($token);
    }

    public function persistProcess(Process $process): void
    {
        $this->dal->persistProcess($process);
    }

    public function getToken(string $id): Token
    {
        return $this->dal->getToken($id);
    }

    public function setLogException(bool $logException): void
    {
        $this->logException = $logException;
    }
}
