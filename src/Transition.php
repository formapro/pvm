<?php
namespace Formapro\Pvm;

use Makasim\Yadm\ValuesTrait;

class Transition
{
    use ValuesTrait;

    const STATE_OPENED = 'opened';
    const STATE_PASSED = 'passed';
    const STATE_INTERRUPTED = 'interrupted';

    /**
     * @var Process
     */
    private $_process;

    /**
     * @var Node
     */
    private $_from;

    /**
     * @var Node
     */
    private $_to;

    public function __construct()
    {
        $this->setId(UUID::generate());
        $this->setWeight(1);
        $this->setAsync(false);
        $this->setActive(true);
        $this->setState(self::STATE_OPENED);
    }

    public function setId($id)
    {
        $this->setValue('id', $id);
    }

    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * @param Process $process
     */
    public function setProcess(Process $process)
    {
        $this->_process = $process;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->_process;
    }

    /**
     * @return Node
     */
    public function getFrom()
    {
        if (null === $this->_from && ($id = $this->getValue('from'))) {
            $this->_from = $this->_process->getNode($id);
        }

        return $this->_from;
    }

    /**
     * @param Node $node
     */
    public function setFrom(Node $node)
    {
        $this->_from = $node;
        $this->setValue('from', $node->getId());
    }

    /**
     * @return Node
     */
    public function getTo()
    {
        if (null === $this->_to && ($id = $this->getValue('to'))) {
            $this->_to = $this->_process->getNode($id);
        }

        return $this->_to;
    }

    /**
     * @param Node $node
     */
    public function setTo(Node $node)
    {
        $this->_to = $node;
        $this->setValue('to', $node->getId());
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->getValue('weight');
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->setValue('weight', $weight);
    }

    /**
     * @return boolean
     */
    public function isAsync()
    {
        return $this->getValue('async');
    }

    /**
     * @param boolean $async
     */
    public function setAsync($async)
    {
        $this->setValue('async', (bool) $async);
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->getValue('active');
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->setValue('active', (bool) $active);
    }

    public function setPassed()
    {
        $this->setState(self::STATE_PASSED);
    }

    public function setInterrupted()
    {
        $this->setState(self::STATE_INTERRUPTED);
    }

    private function setState($state)
    {
        $this->setValue('state', $state);
    }
}
