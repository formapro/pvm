<?php
namespace Formapro\Pvm;

use Makasim\Values\ValuesTrait;

class Transition
{
    use ValuesTrait;

    const STATE_OPENED = 'opened';
    const STATE_PASSED = 'passed';
    const STATE_INTERRUPTED = 'interrupted';

    /**
     * @var Node
     */
    private $from;

    /**
     * @var Node
     */
    private $to;

    /**
     * @var int
     */
    private $weight;

    /**
     * @var boolean
     */
    private $async;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var string
     */
    private $state;

    /**
     * @param Node $from
     * @param Node $to
     */
    public function __construct(Node $from = null, Node $to)
    {
        $this->from = $from;
        $this->to = $to;
        $this->weight = 1;
        $this->async = false;
        $this->active = true;
        $this->state = self::STATE_OPENED;

        $this->setId(UUID::generate());
    }

    public function setId($id)
    {
        $this->setSelfValue('id', $id);
    }

    public function getId()
    {
        return $this->getSelfValue('id');
    }

    /**
     * @return Node
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Node
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return boolean
     */
    public function isAsync()
    {
        return $this->async;
    }

    /**
     * @param boolean $async
     */
    public function setAsync($async)
    {
        $this->async = $async;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    public function setPassed()
    {
        $this->state = self::STATE_PASSED;
    }

    public function setInterrupted()
    {
        $this->state = self::STATE_INTERRUPTED;
    }
}
