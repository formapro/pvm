<?php
namespace Formapro\Pvm;

use function Makasim\Values\get_value;
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

    /**
     * @param Transition $transition
     */
    public function setTransition(Transition $transition): void
    {
        set_value($this, 'transition', $transition->getId());
    }

    /**
     * @return Transition
     */
    public function getTransition(): ?Transition
    {
        if ($id = get_value($this, 'transition')) {
            return $this->_process->getTransition($id);
        }

        return null;
    }
}
