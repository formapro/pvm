<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessStorage;
use Formapro\Pvm\Token;
use Formapro\Pvm\TokenContext;
use Formapro\Pvm\Uuid;
use function Makasim\Values\get_value;
use function Makasim\Values\set_value;
use Makasim\Yadm\Storage;

class StandaloneTokenContext implements TokenContext
{
    /**
     * @var ProcessStorage
     */
    private $processStorage;

    /**
     * @var Storage
     */
    private $tokenStorage;

    public function __construct(ProcessStorage $processStorage, Storage $tokenStorage)
    {
        $this->processStorage = $processStorage;
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
            throw TokenException::notFound($id);
        }

        if ($process->getId() !== get_value($token, 'processId')) {
            throw new TokenException('Another process token requested.');
        }

        $token->setProcess($process);

        return $token;
    }

    public function getToken(string $id): Token
    {
        /** @var Token $token */
        if (false == $token = $this->tokenStorage->findOne(['id' => $id])) {
            throw TokenException::notFound($id);
        }

        $processId = get_value($token, 'processId');
        if (false == $process = $this->processStorage->get($processId)) {
            throw new TokenException(sprintf('The process "%s" could not be found', $processId));
        }

        $token->setProcess($process);

        return $token;
    }

    public function persist(Token $token): void
    {
        $this->tokenStorage->update($token);
        $this->processStorage->persist($token->getProcess());
    }
}
