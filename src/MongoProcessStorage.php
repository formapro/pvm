<?php
namespace Formapro\Pvm;

use function Makasim\Yadm\get_object_id;
use Makasim\Yadm\MongodbStorage;

class MongoProcessStorage implements ProcessStorage
{
    /**
     * @var MongodbStorage
     */
    private $processStorage;

    /**
     * @var MongodbStorage
     */
    private $executionStorage;

    /**
     * @param MongodbStorage $processStorage
     * @param MongodbStorage $executionStorage
     */
    public function __construct(MongodbStorage $processStorage, MongodbStorage $executionStorage)
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
     * @return Process
     */
    public function findExecution($id)
    {
        return $this->executionStorage->findOne(['id' => $id]);
    }
}