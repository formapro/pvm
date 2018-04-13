<?php
namespace Formapro\Pvm;

interface TokenLockerInterface
{
    public function locked(string $tokenId): bool;

    public function lock(string $tokenId): void;

    public function unlock(string $tokenId): void;
}
