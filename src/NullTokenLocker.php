<?php
namespace Formapro\Pvm;

class NullTokenLocker implements TokenLockerInterface
{
    public function lock(string $token): void
    {
    }

    public function unlock(string $token): void
    {
    }
}
