<?php
namespace Formapro\Pvm;

class AsyncTransitionIsNotConfigured implements AsyncTransition
{
    public function transition(array $tokens)
    {
        throw new \LogicException('The async transitions is not configured. To be able to start using async transition you have to use enqueue library and its bridge classes or implement AsyncTransition interface yourself and provide it to the process engine.');
    }
}
