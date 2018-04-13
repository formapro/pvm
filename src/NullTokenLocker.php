<?php
namespace Formapro\Pvm;

class NullTokenLocker implements TokenLockerInterface
{
    public function lock(string $token, bool $blocking = true): void
    {
    }

    public function unlock(string $token): void
    {
    }

    public function locked(string $tokenId): bool
    {
        return false;
    }
}
