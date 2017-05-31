<?php
namespace Formapro\Pvm;

use function Makasim\Yadm\get_object_id;
use Makasim\Yadm\Storage;

class MongoProcessStorage implements ProcessStorage
{
    /**
     * @var Storage
     */
    private $processStorage;

    /**
     * @var Storage
     */
    private $executionStorage;

    /**
     * @param Storage $processStorage
     * @param Storage $executionStorage
     */
    public function __construct(Storage $processStorage, Storage $executionStorage)
    {
        $this->processStorage = $processStorage;
        $this->executionStorage = $executionStorage;
    }

    public function persist(Process $process)
    {
        if (get_object_id($process)) {
            $this->processStorage->update($process);
        } else {
            $this->processStorage->insert($process);
        }
    }

    public function saveExecution(Process $process)
    {
        if (get_object_id($process)) {
            $this->executionStorage->update($process);
        } else {
            $this->executionStorage->insert($process);
        }
    }

    /**
     * @param string $id
     *
     * @return Process|object
     */
    public function findExecution($id)
    {
        return $this->executionStorage->findOne(['id' => $id]);
    }
}