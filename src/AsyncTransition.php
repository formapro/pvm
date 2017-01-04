<?php
namespace Formapro\Pvm;

interface AsyncTransition
{
    public function transition(array $tokens);
}
