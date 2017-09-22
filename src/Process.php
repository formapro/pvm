<?php
namespace Formapro\Pvm;

use Formapro\Pvm\Yadm\CreateTrait;
use Makasim\Values\ObjectsTrait;
use Makasim\Values\ValuesTrait;

class Process
{
    use ValuesTrait {
        getValue as public;
        setValue as public;
    }

    use ObjectsTrait;
    use CreateTrait;

    /**
     * @var Node[]
     */
    protected $_nodes;

    /**
     * @var Transition[]
     */
    protected $_transitions;

    /**
     * @var Token[]
     */
    private $_tokens;

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
        if (null === $node = $this->getObject('nodes.'.$id)) {
            throw new \LogicException('Not found');
        }

        return $this->_nodes[$id] = $node;
    }

    public function getNodes()
    {
        return $this->getObjects('nodes');
    }

    /**
     * @return Transition[]
     */
    public function getTransitions()
    {
        return $this->getObjects('transitions');
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
        if (null === $transition = $this->getObject('transitions.'.$id)) {
            throw new \LogicException('Not found');
        }

        return $this->_transitions[$id] = $transition;
    }

    /**
     * @param Node $node
     *
     * @return Transition[]
     */
    public function getInTransitions(Node $node)
    {
        $inTransitions = $this->getValue('inTransitions.'.$node->getId(), []);

        $transitions = [];
        foreach ($inTransitions as $id) {
            $transitions[] = $this->getTransition($id);
        }

        return $transitions;
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
     * @param Node $node
     * @param string $name
     *
     * @return Transition
     */
    public function getOutTransitionWithName(Node $node, $name)
    {
        $outTransitions = $this->getValue('outTransitions.'.$node->getId(), []);

        foreach ($outTransitions as $id) {
            $transition = $this->getTransition($id);
            if ($transition->getName() == $name) {
                return $transition;
            }
        }

        throw new \LogicException(sprintf('The transition with name %s could not be found', $name));
    }

    /**
     * @param Node $node
     *
     * @return Transition[]
     */
    public function getOutTransitionsWithName(Node $node, $name)
    {
        $outTransitions = $this->getValue('outTransitions.'.$node->getId(), []);

        $outTransitionsWithName = [];
        foreach ($outTransitions as $id) {
            $transition = $this->getTransition($id);
            if ($transition->getName() == $name) {
                $outTransitionsWithName[] = $transition;
            }
        }

        return $outTransitionsWithName;
    }

    /**
     * @param Node $node
     */
    public function registerNode(Node $node)
    {
        $node->setProcess($this);
        $this->setObject('nodes.'.$node->getId(), $node);
    }

    /**
     * @return Node
     */
    public function createNode()
    {
        $node = Node::create();
        $node->setProcess($this);

        $this->setObject('nodes.'.$node->getId(), $node);

        return $node;
    }

    public function createTransition(Node $from = null, Node $to = null, $name = null)
    {
        $transition = Transition::create();
        $transition->setName($name);
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

    public function breakTransition(Transition $transition, Node $node, $newName = null)
    {
        $oldTo = $transition->getTo();
        $transition->setTo($node);

        $newTransition = $this->createTransition($node, $oldTo);
        $newTransition->setName($newName);

        return $newTransition;
    }

    /**
     * @param Transition $transition
     *
     * @return Token
     */
    public function createToken(Transition $transition)
    {
        $token = Token::create();
        $token->setProcess($this);
        $token->setTransition($transition);

        $this->setObject('tokens.'.$token->getId(), $token);

        return $token;
    }

    /**
     * @return Token[]
     */
    public function getTokens()
    {
        return $this->getObjects('tokens');
    }

    public function getToken($id)
    {
        /** @var Token $token */
        foreach ($this->getTokens() as $token) {
            if ($token->getId() === $id) {

                return $token;
            }
        }
    }
}
