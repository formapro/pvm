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

    /**
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(Process $process)
    {
        get_object_id($process) ? $this->storage->update($process) : $this->storage->insert($process);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if(false == $process = $this->storage->findOne(['id' => $id])) {
            throw new \LogicException(sprintf('The process with id "%s" could not be found', $id));
        }

        return$process;
    }

    /**
     * @return Storage
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }
}
