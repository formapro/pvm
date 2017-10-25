<?php
namespace Formapro\Pvm;

class NullTokenLocker implements TokenLockerInterface
{
    public function lock(Token $token): void
    {
    }

    public function unlock(Token $token): void
    {
    }
}
