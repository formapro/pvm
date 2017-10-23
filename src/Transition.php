<?php
namespace Formapro\Pvm;

use function Makasim\Values\get_value;
use function Makasim\Values\set_value;
use Makasim\Values\ValuesTrait;

class Transition
{
    const SCHEMA = 'http://pvm.forma-pro.com/schemas/Transition.json';

    use ValuesTrait {
        setValue as public;
        getValue as public;
    }
    use CreateTrait;

    const STATE_OPENED = 'opened';
    const STATE_PASSED = 'passed';
    const STATE_WAITING = 'waiting';
    const STATE_INTERRUPTED = 'interrupted';

    /**
     * @var Process
     */
    private $_process;

    public function __construct()
    {
        $this->setId(Uuid::generate());
        $this->setWeight(1);
        $this->setAsync(false);
        $this->setActive(true);
        $this->setState(self::STATE_OPENED);
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        set_value($this, 'id', $id);
    }

    public function getName(): ?string
    {
        return get_value($this, 'name');
    }

    /**
     * @param string $name
     */
    public function setName(string $name = null): void
    {
        set_value($this, 'name', $name);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return get_value($this, 'id');
    }

    /**
     * @param Process $process
     */
    public function setProcess(Process $process): void
    {
        $this->_process = $process;
    }

    /**
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->_process;
    }

    /**
     * @return Node
     */
    public function getFrom(): ?Node
    {
        if ($id = get_value($this, 'from')) {
            return $this->_process->getNode($id);
        }

        return null;
    }

    /**
     * @param Node $node
     */
    public function setFrom(Node $node): void
    {
        set_value($this, 'from', $node->getId());
    }

    /**
     * @return Node
     */
    public function getTo(): ?Node
    {
        if ($id = get_value($this, 'to')) {
            return $this->_process->getNode($id);
        }

        return null;
    }

    /**
     * @param Node $node
     */
    public function setTo(Node $node): void
    {
        set_value($this, 'to', $node->getId());
    }

    /**
     * @return int
     */
    public function getWeight(): ?int
    {
        return get_value($this, 'weight');
    }

    /**
     * @param int $weight
     */
    public function setWeight(int $weight = null): void
    {
        set_value($this, 'weight', $weight);
    }

    /**
     * @return boolean
     */
    public function isAsync(): bool
    {
        return get_value($this, 'async');
    }

    /**
     * @param boolean $async
     */
    public function setAsync(bool $async): void
    {
        set_value($this, 'async', $async);
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return get_value($this, 'active');
    }

    /**
     * @param boolean $active
     */
    public function setActive(bool $active): void
    {
        set_value($this, 'active', $active);
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return get_value($this, 'state');
    }

    public function setPassed(): void
    {
        $this->setState(self::STATE_PASSED);
    }

    public function isPassed(): bool
    {
        return $this->getState() === self::STATE_PASSED;
    }

    public function setInterrupted(): void
    {
        $this->setState(self::STATE_INTERRUPTED);
    }

    public function isInterrupted(): bool
    {
        return $this->getState() === self::STATE_INTERRUPTED;
    }

    public function setWaiting(): void
    {
        $this->setState(self::STATE_WAITING);
    }

    public function isWaiting(): bool
    {
        return $this->getState() === self::STATE_WAITING;
    }

    private function setState($state): void
    {
        set_value($this, 'state', $state);
    }
}
