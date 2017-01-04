<?php
namespace Formapro\Pvm;


interface Behavior
{
    /**
     * @param Token $token
     *
     * @return Transition[]
     */
    public function execute(Token $token);
}
