<?php
namespace Formapro\Pvm;

interface TokenLockerInterface
{
    public function lock(Token $token): void;

    public function unlock(Token $token): void;
}
