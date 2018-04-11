<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessStorage;
use Formapro\Pvm\Token;
use function Makasim\Values\get_value;
use function Makasim\Yadm\get_object_id;
use Makasim\Yadm\Storage;

class MongoProcessStorage implements ProcessStorage
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var Storage|null
     */
    private $tokenStorage;

    public function __construct(Storage $processStorage, Storage $tokenStorage = null)
    {
        $this->storage = $processStorage;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(Process $process): void
    {
        get_object_id($process) ? $this->storage->update($process) : $this->storage->insert($process);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id): Process
    {
        /** @var Process $process */
        if(false == $process = $this->storage->findOne(['id' => $id])) {
            throw new \LogicException(sprintf('The process with id "%s" could not be found', $id));
        }

        return $process;
    }

    /**
     * @return Storage
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getByToken(string $tokenString): Process
    {
        if ($this->tokenStorage) {
            /** @var Token $token */
            if (false == $token = $this->tokenStorage->findOne(['id' => $tokenString])) {
                throw new \LogicException(sprintf('The token "%s" could not be found', $tokenString));
            }

            return $this->get(get_value($token, 'processId'));
        }

        /** @var Process $process */
        if (false == $process = $this->storage->findOne(['tokens.'.$tokenString => ['$exists' => true]])) {
            throw new \LogicException(sprintf('The process with token "%s" could not be found', $tokenString));
        }

        return $process;
    }
}
