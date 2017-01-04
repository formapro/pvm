<?php
namespace Formapro\Pvm;

class Token
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var Transition
     */
    private $transition;

    public function __construct(Process $process, Transition $transition)
    {
        $this->process = $process;
        $this->transition = $transition;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param Transition $transition
     */
    public function setTransition(Transition $transition)
    {
        $this->transition = $transition;
    }

    /**
     * @return Transition
     */
    public function getTransition()
    {
        return $this->transition;
    }
}
