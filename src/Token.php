<?php
namespace Formapro\Pvm;

use function Makasim\Values\add_object;
use function Makasim\Values\add_value;
use function Makasim\Values\get_object;
use function Makasim\Values\get_objects;
use function Makasim\Values\get_value;
use function Makasim\Values\set_object;
use function Makasim\Values\set_value;

class Token
{
    const SCHEMA = 'http://pvm.forma-pro.com/schemas/Token.json';

    use CreateTrait;

    protected $values = [];

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
    public function setId(string $id): void
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
    public function setProcess(Process $process): void
    {
        $this->_process = $process;
    }

    public function addTransition(TokenTransition $transition): void
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

        return $transitions;
    }
}
