<?php
namespace Formapro\Pvm;

use Makasim\Values\ObjectsTrait;
use Makasim\Values\ValuesTrait;

class Process
{
    use ValuesTrait;
    use ObjectsTrait;

    /**
     * @var Node[]
     */
    private $nodes;

    /**
     * @var Transition[]
     */
    private $transitions;

    /**
     * @var Transition[]
     */
    private $inTransitions;

    /**
     * @var Transition[]
     */
    private $outTransitions;

    /**
     * @var Token[]
     */
    private $tokens;

    public function __construct()
    {
        $this->nodes = [];
        $this->outTransitions = [];
    }

    public function setId($id)
    {
        $this->setSelfValue('id', $id);
    }

    public function getId()
    {
        return $this->getSelfValue('id');
    }

    public function setExecutionId($id)
    {
        $this->setSelfValue('executionId', $id);
    }

    public function getExecutionId()
    {
        return $this->getSelfValue('executionId');
    }

    /**
     * @return Node[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @param Node $node
     *
     * @return Transition[]
     */
    public function getOutTransitionsForNode(Node $node)
    {
        $outTransitions = $this->getSelfValue('outTransitions', []);

        if (isset($outTransitions[$node->getId()])) {
            $transitions = [];
            foreach ($this->getSelfObjects('transitions', Transition::class) as $transition) {
                if (in_array($transition->getId(), $outTransitions[$node->getId()])) {
                    $transitions[] = $transition;
                }
            }

            return $transitions;
        }
    }

    /**
     * @param Node $node
     */
    public function addNode(Node $node)
    {
        $this->addSelfObject('nodes', $node);
    }

    public function addTransition(Transition $transition)
    {
        $this->addSelfObject('transitions', $transition);

        if ($transition->getFrom()) {
            $outTransitions = $this->getSelfValue('outTransitions', []);
            $outTransitions[$transition->getFrom()->getId()][] = $transition->getId();
            $this->setSelfValue('outTransitions', $outTransitions);
        }

        if ($transition->getTo()) {
            $inTransitions = $this->getSelfValue('inTransitions', []);
            $inTransitions[$transition->getTo()->getId()][] = $transition->getId();
            $this->setSelfValue('inTransitions', $inTransitions);
        }
    }

    /**
     * @param Transition $transition
     *
     * @return Token
     */
    public function createToken(Transition $transition)
    {
        return $this->tokens[] = new Token($this, $transition);
    }
}
