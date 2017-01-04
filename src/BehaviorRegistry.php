<?php
namespace Formapro\Pvm;

interface BehaviorRegistry
{
    /**
     * @param string $name
     *
     * @return Behavior
     */
    public function get(string $name) :Behavior;
}
