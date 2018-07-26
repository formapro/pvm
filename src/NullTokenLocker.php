<?php
namespace Formapro\Pvm;

class NullTokenLocker implements TokenLockerInterface
{
    public function lock(string $token, bool $blocking = true)
    {
    }

    public function unlock(string $token)
    {
    }

    public function locked(string $tokenId): bool
    {
        return false;
    }
}
