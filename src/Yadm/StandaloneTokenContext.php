<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\Process;
use Formapro\Pvm\Token;
use Formapro\Pvm\TokenContext;
use Formapro\Pvm\TokenTransition;
use Formapro\Pvm\Transition;
use Formapro\Pvm\Uuid;
use function Makasim\Values\get_value;
use function Makasim\Values\set_value;
use Makasim\Yadm\Storage;

class StandaloneTokenContext implements TokenContext
{
    /**
     * @var Storage
     */
    private $tokenStorage;

    public function __construct(Storage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function createProcessToken(Process $process, string $id = null): Token
    {
        $token = Token::create();
        $token->setId($id ?: Uuid::generate());
        $token->setProcess($process);

        set_value($token, 'processId', $process->getId());

        $this->tokenStorage->insert($token);

        return $token;
    }

    public function forkProcessToken(Token $token, string $id = null): Token
    {
        return $this->createProcessToken($token->getProcess(), $id);
    }

    public function getProcessTokens(Process $process): \Traversable
    {
        foreach ($this->tokenStorage->find(['processId' => $process->getId()]) as $token) {
            /** @var Token $token */

            $token->setProcess($process);

            yield $token;
        }
    }

    public function getProcessToken(Process $process, string $id): Token
    {
        /** @var Token $token */
        if (false == $token = $this->tokenStorage->findOne(['id' => $id])) {
            throw new \LogicException(sprintf('Token Not found. Id: "%s"', $id));
        }

        if ($process->getId() !== get_value($token, 'processId')) {
            throw new \LogicException('Another process token requested.');
        }

        $token->setProcess($process);

        return $token;
    }

    public function persist(Token $token): void
    {
        $this->tokenStorage->update($token);
    }
}
