<?php
namespace Formapro\Pvm;

use function Makasim\Values\add_value;
use function Makasim\Values\get_object;
use function Makasim\Values\get_objects;
use function Makasim\Values\get_value;
use function Makasim\Values\set_object;
use function Makasim\Values\set_value;
use Makasim\Values\ValuesTrait;

class Process
{
    const SCHEMA = 'http://pvm.forma-pro.com/schemas/Process.json';

    use ValuesTrait {
        getValue as public;
        setValue as public;
    }

    use CreateTrait;

    protected $objects = [];

    public function setId(string $id): void
    {
        set_value($this, 'id', $id);
    }

    public function getId(): string
    {
        return get_value($this, 'id');
    }

    public function setExecutionId(string $id): void
    {
        set_value($this, 'executionId', $id);
    }

    public function getExecutionId(): string
    {
        return get_value($this, 'executionId');
    }

    /**
     * @param string $id
     *
     * @return Node
     */
    public function getNode(string $id): Node
    {
        /** @var Node $node */
        if (null === $node = get_object($this, 'nodes.'.$id, ClassClosure::create())) {
            throw new \LogicException('Not found');
        }

        $node->setProcess($this);

        return $node;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        $nodes = [];
        foreach (get_objects($this, 'nodes', ClassClosure::create()) as $node) {
            /** @var Node $node */

            $node->setProcess($this);

            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array
    {
        $transitions = [];
        foreach (get_objects($this, 'transitions', ClassClosure::create()) as $transition) {
            /** @var Transition $transition */

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
    public function getTransition(string $id): Transition
    {
        /** @var Transition $transition */
        if (null === $transition = get_object($this, 'transitions.'.$id, ClassClosure::create())) {
            throw new \LogicException('Not found');
        }

        $transition->setProcess($this);

        return $transition;
    }

    /**
     * @param Node $node
     *
     * @return Transition[]
     */
    public function getInTransitions(Node $node): array
    {
        $inTransitions = get_value($this, 'inTransitions.'.$node->getId(), []);

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
    public function getOutTransitions(Node $node): array
    {
        $outTransitions = get_value($this, 'outTransitions.'.$node->getId(), []);

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
     * @return Transition[]
     */
    public function getOutTransitionsWithName(Node $node, string $name): array
    {
        $outTransitions = get_value($this, 'outTransitions.'.$node->getId(), []);

        $transitions = [];
        foreach ($outTransitions as $id) {
            $transition = $this->getTransition($id);
            if ($transition->getName() == $name) {
                $transitions[] = $transition;
            }
        }

        return $transitions;
    }

    /**
     * @param Node $node
     */
    public function registerNode(Node $node)
    {
        $node->setProcess($this);

        set_object($this, 'nodes.'.$node->getId(), $node);
    }

    /**
     * @return Node
     */
    public function createNode()
    {
        $node = Node::create();
        $node->setProcess($this);

        set_object($this, 'nodes.'.$node->getId(), $node);

        return $node;
    }

    /**
     * @param Node|null $from
     * @param Node|null $to
     * @param string|null $name
     *
     * @return Transition
     */
    public function createTransition(Node $from = null, Node $to = null, string $name = null)
    {
        $transition = Transition::create();
        $transition->setName($name);
        $transition->setProcess($this);
        $from && $transition->setFrom($from);
        $to && $transition->setTo($to);

        set_object($this, 'transitions.'.$transition->getId(), $transition);

        if ($transition->getFrom()) {
            add_value($this, 'outTransitions.'.$transition->getFrom()->getId(), $transition->getId());
        }

        if ($transition->getTo()) {
            add_value($this, 'inTransitions.'.$transition->getTo()->getId(), $transition->getId());
        }

        return $transition;
    }

    public function breakTransition(Transition $transition, Node $node, string $newName = null): Transition
    {
        $oldTo = $transition->getTo();
        $transition->setTo($node);

        $newTransition = $this->createTransition($node, $oldTo);
        $newTransition->setName($newName);
        $newTransition->setProcess($this);

        return $newTransition;
    }

    /**
     * @param Transition $transition
     *
     * @return Token
     */
    public function createToken(Transition $transition): Token
    {
        $token = Token::create();
        $token->setProcess($this);
        $token->setTransition($transition);

        set_object($this, 'tokens.'.$token->getId(), $token);

        return $token;
    }

    /**
     * @return Token[]
     */
    public function getTokens(): array
    {
        $tokens = [];
        foreach (get_objects($this, 'tokens', ClassClosure::create()) as $token) {
            /** @var Token $token */

            $token->setProcess($this);

            $tokens[] = $token;
        }

        return $tokens;
    }

    public function getToken(string $id): Token
    {
        /** @var Token $token */
        if (null === $token = get_object($this, 'tokens.'.$id, ClassClosure::create())) {
            throw new \LogicException('Not found');
        }

        $token->setProcess($this);

        return $token;
    }
}
