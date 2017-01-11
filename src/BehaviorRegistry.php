<?php
namespace Formapro\Pvm;

interface BehaviorRegistry
{
    /**
     * @param string $name
     *
     * @return Behavior|SignalBehavior
     */
    public function get($name);
}
