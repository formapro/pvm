<?php
namespace Formapro\Pvm;

class DefaultBehaviorRegistry implements BehaviorRegistry
{
    /**
     * @var Behavior[]
     */
    private $behaviors;

    /**
     * @param string   $name
     * @param Behavior $behavior
     */
    public function register($name, Behavior $behavior)
    {
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
