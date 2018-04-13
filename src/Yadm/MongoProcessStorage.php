<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessStorage;
use function Makasim\Yadm\get_object_id;
use Makasim\Yadm\Storage;

class MongoProcessStorage implements ProcessStorage
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(Storage $processStorage)
    {
        $this->storage = $processStorage;
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
        /** @var Process $process */
        if (false == $process = $this->storage->findOne(['tokens.'.$tokenString => ['$exists' => true]])) {
            throw new \LogicException(sprintf('The process with token "%s" could not be found', $tokenString));
        }

        return $process;
    }
}
