<?php
namespace Formapro\Pvm;

interface AsyncTransition
{
    /**
     * @param Token[]|array $tokens
     */
    public function transition(array $tokens);
}
