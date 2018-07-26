<?php
namespace Formapro\Pvm;

interface DAL
{
    public function createProcessToken(Process $process, string $id = null): Token;

    public function forkProcessToken(Token $token, string $id = null): Token;

    /**
     * @return Token[]|\Traversable
     */
    public function getProcessTokens(Process $process): \Traversable;

    public function getProcessToken(Process $process, string $id): Token;

    public function getToken(string $id): Token;

    public function persistToken(Token $token);

    public function persistProcess(Process $process);
}
