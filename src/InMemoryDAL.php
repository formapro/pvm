<?php
namespace Formapro\Pvm;

use function Makasim\Values\get_object;
use function Makasim\Values\get_objects;
use function Makasim\Values\set_object;

class InMemoryDAL implements DAL
{
    public function createProcessToken(Process $process, string $id = null): Token
    {
        $token = Token::create();
        $token->setId($id ?: Uuid::generate());
        $token->setProcess($process);

        set_object($process, 'tokens.'.$token->getId(), $token);

        return $token;
    }

    public function forkProcessToken(Token $token, string $id = null): Token
    {
        return $this->createProcessToken($token->getProcess(), $id);
    }

    public function getProcessTokens(Process $process): \Traversable
    {
        foreach (get_objects($process, 'tokens', ClassClosure::create()) as $token) {
            /** @var Token $token */

            $token->setProcess($process);

            yield $token;
        }
    }

    public function getProcessToken(Process $process, string $id): Token
    {
        /** @var Token $token */
        if (null === $token = get_object($process, 'tokens.'.$id, ClassClosure::create())) {
            throw TokenException::notFound($id);
        }

        $token->setProcess($process);

        return $token;
    }

    public function persistToken(Token $token): void
    {
    }

    public function persistProcess(Process $process): void
    {
    }

    public function getToken(string $id): Token
    {
        throw new \LogicException('The context does not support this method.');
    }
}
