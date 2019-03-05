<?php
namespace Formapro\Pvm;

use Formapro\Pvm\Builder\NodeBuilder;
use Formapro\Pvm\Builder\TransitionBuilder;
use function Formapro\Values\add_value;
use function Formapro\Values\get_value;
use function Formapro\Values\set_object;

class ProcessBuilder
{
    private $process;

    public function __construct(Process $process = null)
    {
        $this->process = $process ?: Process::create();

        if (false == get_value($this->process, 'id')) {
            $this->process->setId(Uuid::generate());
        }
    }

    public function setId(string $id): self
    {
        $this->process->setId($id);

        return $this;
    }

    public function registerNode(Node $node): NodeBuilder
    {
        $node->setProcess($this->process);

        set_object($this->process, 'nodes.'.$node->getId(), $node);

        return new NodeBuilder($this, $node);
    }

    public function createNode(string $id = null, string $behavior = null): NodeBuilder
    {
        $node = Node::create();
        $node->setId($id ?: Uuid::generate());
        $node->setBehavior($behavior);
        $node->setProcess($this->process);

        set_object($this->process, 'nodes.'.$node->getId(), $node);

        return new NodeBuilder($this, $node);
    }

    /**
     * @param Node|string|null $from
     * @param Node|string $to
     * @param string|null $name
     *
     * @return TransitionBuilder
     */
    public function createTransition($from, $to, string $name = null): TransitionBuilder
    {
        if (is_string($from)) {
            $from = $this->process->getNode($from);
        }
        if ($from && false == $from instanceof Node) {
            throw new \InvalidArgumentException('The from argument is invalid. Must be string or Node instance.');
        }

        if (is_string($to)) {
            $to = $this->process->getNode($to);
        }
        if (false == $to instanceof Node) {
            throw new \InvalidArgumentException('The from argument is invalid. Must be string or Node instance.');
        }


        $transition = Transition::create();
        $transition->setName($name);
        $transition->setProcess($this->process);
        $from && $transition->setFrom($from);
        $to && $transition->setTo($to);

        set_object($this->process, 'transitions.'.$transition->getId(), $transition);

        if ($transition->getFrom()) {
            add_value($this->process, 'outTransitions.'.$transition->getFrom()->getId(), $transition->getId());
        }

        if ($transition->getTo()) {
            add_value($this->process, 'inTransitions.'.$transition->getTo()->getId(), $transition->getId());
        }

        return new TransitionBuilder($this, $transition);
    }

    /**
     * @param Node|string $to
     * @param string|null $name
     *
     * @return TransitionBuilder
     */
    public function createStartTransition($to, string $name = null): TransitionBuilder
    {
        return $this->createTransition(null, $to, $name);
    }

    public function breakTransition(Transition $transition, Node $node, string $newName = null): TransitionBuilder
    {
        $oldTo = $transition->getTo();
        $transition->setTo($node);

        $newTransition = $this->createTransition($node, $oldTo)->getTransition();
        $newTransition->setName($newName);
        $newTransition->setProcess($this->process);

        return new TransitionBuilder($this, $newTransition);
    }
    
    public function getProcess(): Process
    {
        return $this->process;
    }
}
