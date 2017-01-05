<?php
namespace Formapro\Pvm;

use Makasim\Yadm\ValuesTrait;

class Token
{
    use ValuesTrait;

    /**
     * @var Process
     */
    private $_process;

    /**
     * @var Transition
     */
    private $_transition;

    public function __construct()
    {
        $this->setId(UUID::generate());
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->setValue('id', $id);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * @return Process
     */
    public function getProcess()
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

    /**
     * @param Transition $transition
     */
    public function setTransition(Transition $transition)
    {
        $this->_transition = $transition;
        $this->setValue('transition', $transition->getId());
    }

    /**
     * @return Transition
     */
    public function getTransition()
    {
        if (null === $this->_transition && ($id = $this->getValue('transition'))) {
            $this->_transition = $this->_process->getTransition($id);
        }

        return $this->_transition;
    }
}
