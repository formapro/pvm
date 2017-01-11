<?php
namespace Formapro\Pvm;

use Makasim\Yadm\ObjectsTrait;
use Makasim\Yadm\ValuesTrait;

class Process
{
    use ValuesTrait {
        getValue as public;
        setValue as public;
    }
    use ObjectsTrait;

    private $_nodes;

    private $_transitions;

    /**
     * @var Token[]
     */
    private $_tokens;
    
    private $hash;

    public function __construct()
    {
        $this->hash = spl_object_hash($this);
        $this->objectBuilder = function ($object) {
            $hash = spl_object_hash($this);
            $object->setProcess($this);
        };
    }

    public function setId($id)
    {
        $this->setValue('id', $id);
    }

    public function getId()
    {
        return $this->getValue('id');
    }

    public function setExecutionId($id)
    {
        $this->setValue('executionId', $id);
    }

    public function getExecutionId()
    {
        return $this->getValue('executionId');
    }

    /**
     * @param string $id
     *
     * @return Node
     */
    public function getNode($id)
    {
        if (isset($this->_nodes[$id])) {
            return $this->_nodes[$id];
        }

        /** @var Node $node */
        if (null === $node = $this->getObject('nodes.'.$id, Node::class)) {
            throw new \LogicException('Not found');
        }

        //??
        $node->setProcess($this);

        return $this->_nodes[$id] = $node;
    }

    public function getNodes()
    {
        return $this->getObjects('nodes', Node::class);
    }

    /**
     * @return Transition[]
     */
    public function getTransitions()
    {
        $transitions = [];
        foreach ($this->getObjects('transitions', Transition::class) as $transition) {
            $transition->setProcess($this);
            $transitions[] = $transition;
        }

        return $transitions;
    }

    /**
     * @param string $id
     *
     * @return Transition
     */
    public function getTransition($id)
    {
        if (isset($this->_transitions[$id])) {
            return $this->_transitions[$id];
        }

        /** @var Transition $transition */
        if (null === $transition = $this->getObject('transitions.'.$id, Transition::class)) {
            throw new \LogicException('Not found');
        }

        $transition->setProcess($this);

        return $this->_transitions[$id] = $transition;
    }

    /**
     * @param Node $node
     *
     * @return Transition[]
     */
    public function getOutTransitions(Node $node)
    {
        $outTransitions = $this->getValue('outTransitions.'.$node->getId(), []);

        $transitions = [];
        foreach ($outTransitions as $id) {
            $transitions[] = $this->getTransition($id);
        }

        return $transitions;
    }

    /**
     * @return Node
     */
    public function createNode()
    {
        $node = new Node();
        $node->setProcess($this);

        $this->setObject('nodes.'.$node->getId(), $node);

        return $node;
    }

    public function createTransition(Node $from = null, Node $to = null)
    {
        $transition = new Transition();
        $transition->setProcess($this);
        $from && $transition->setFrom($from);
        $to && $transition->setTo($to);

        $this->setObject('transitions.'.$transition->getId(), $transition);

        if ($transition->getFrom()) {
            $this->addValue('outTransitions.'.$transition->getFrom()->getId(), $transition->getId());
        }

        if ($transition->getTo()) {
            $this->addValue('inTransitions.'.$transition->getTo()->getId(), $transition->getId());
        }

        return $transition;
    }

    /**
     * @param Transition $transition
     *
     * @return Token
     */
    public function createToken(Transition $transition)
    {
        $token = new Token();
        $token->setProcess($this);
        $token->setTransition($transition);

        $this->setObject('tokens.'.$token->getId(), $token);

        return $token;
    }

    public function getToken($id)
    {
        /** @var Token $token */
        foreach ($this->getObjects('tokens', Token::class) as $token) {
            if ($token->getId() === $id) {
                $token->setProcess($this);

                return $token;
            }
        }
    }
}
