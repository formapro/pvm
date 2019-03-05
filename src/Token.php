<?php
namespace Formapro\Pvm;

use function Formapro\Values\add_object;
use function Formapro\Values\get_objects;
use function Formapro\Values\get_value;
use function Formapro\Values\set_value;
use Formapro\Values\ValuesTrait;

class Token
{
    const SCHEMA = 'http://pvm.forma-pro.com/schemas/Token.json';

    use CreateTrait;
    use ValuesTrait {
        getValue as public;
        setValue as public;
    }

    /**
     * @var Process
     */
    private $_process;

    /**
     * @var TokenTransition
     */
    private $_currentTokenTransition;

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        set_value($this, 'id', $id);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return get_value($this, 'id');
    }

    /**
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->_process;
    }

    /**
     * @param Process $process
     */
    public function setProcess(Process $process)
    {
        $this->_process = $process;
    }

    public function addTransition(TokenTransition $transition)
    {
        $transition->setProcess($this->getProcess());

        add_object($this, 'transitions', $transition);

        $this->_currentTokenTransition = $transition;
    }

    public function getCurrentTransition(): TokenTransition
    {
        if (false == $this->_currentTokenTransition) {
            $transitions = $this->getTransitions();

            $this->_currentTokenTransition = array_pop($transitions);
        }

        return $this->_currentTokenTransition;
    }

    /**
     * @return TokenTransition[]
     */
    public function getTransitions(): array
    {
        $transitions = [];
        foreach (get_objects($this, 'transitions', ClassClosure::create()) as $transition) {
            /** @var TokenTransition $transition */

            $transition->setProcess($this->getProcess());
            $transitions[] = $transition;
        }

        usort($transitions, function(TokenTransition $left, TokenTransition $right) {
            return $left->getTime() <=> $right->getTime();
        });

        return $transitions;
    }

    public function getTo(): Node
    {
        return $this->getCurrentTransition()->getTransition()->getTo();
    }

    public function getFrom(): Node
    {
        return $this->getCurrentTransition()->getTransition()->getFrom();
    }
}
