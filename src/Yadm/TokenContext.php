<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\DefaultTokenContext;
use Formapro\Pvm\Process;
use Formapro\Pvm\Token;
use function Makasim\Yadm\get_object_id;
use Makasim\Yadm\Storage;

class TokenContext extends DefaultTokenContext
{
    /**
     * @var Storage
     */
    private $processStorage;

    public function __construct(Storage $processStorage)
    {
        $this->processStorage = $processStorage;
    }

    public function getToken(string $id): Token
    {
        /** @var Process $process */
        if (false == $process = $this->processStorage->findOne(['tokens.'.$id => ['$exists' => true]])) {
            throw TokenException::notFound(sprintf('The token "%s" could not be found', $id));
        }

        return $this->getProcessToken($process, $id);
    }

    public function persist(Token $token): void
    {
        $process = $token->getProcess();

        get_object_id($process) ?
            $this->processStorage->update($process) :
            $this->processStorage->insert($process)
        ;
    }
}
