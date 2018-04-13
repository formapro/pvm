<?php
namespace Formapro\Pvm;

use function Makasim\Values\get_object;
use function Makasim\Values\get_objects;
use function Makasim\Values\set_object;

class DefaultTokenContext implements TokenContext
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
            throw new \LogicException(sprintf('Token Not found. Id: "%s"', $id));
        }

        $token->setProcess($process);

        return $token;
    }

    public function persist(Token $token): void
    {
        // tokens are stored with the process, nothing to do here
    }
}
