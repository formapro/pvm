<?php
namespace Formapro\Pvm;

interface TokenContext
{
    public function createProcessToken(Process $process, Transition $transition, string $id = null): Token;

    /**
     * @param Process $process
     *
     * @return Token[]|\Traversable
     */
    public function getProcessTokens(Process $process): \Traversable;

    public function getProcessToken(Process $process, string $id): Token;

    public function persist(Token $token): void;
}
