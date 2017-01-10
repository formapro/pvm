<?php
namespace Formapro\Pvm;


interface SignalBehavior
{
    /**
     * @param Token $token
     *
     * @return Transition[]
     */
    public function signal(Token $token);
}
