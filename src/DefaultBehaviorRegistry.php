<?php
namespace Formapro\Pvm;

class DefaultBehaviorRegistry implements BehaviorRegistry
{
    /**
     * @var Behavior[]
     */
    private $behaviors;

    public function __construct(array $behaviors = [])
    {
        foreach ($behaviors as $name => $behavior) {
            $this->register($name, $behavior);
        }
    }

    /**
     * @param string   $name
     * @param callable|Behavior $behavior
     */
    public function register($name, $behavior)
    {
        if (is_callable($behavior)) {
            $behavior = new CallbackBehavior($behavior);
        }
        if (false == $behavior instanceof Behavior) {
            throw new \InvalidArgumentException('The behavior must be callable or instance of Behavior.');
        }

        $this->behaviors[$name] = $behavior;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (false == isset($this->behaviors[$name])) {
            throw new \LogicException(sprintf('Behavior is not registered with name: "%s"', $name));
        }

        return $this->behaviors[$name];
    }
}
