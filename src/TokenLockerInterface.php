<?php
namespace Formapro\Pvm;

interface TokenLockerInterface
{
    public function locked(string $tokenId): bool;

    public function lock(string $tokenId, bool $blocking = true);

    public function unlock(string $tokenId);
}
