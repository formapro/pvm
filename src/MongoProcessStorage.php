<?php
namespace Formapro\Pvm;

use function Makasim\Yadm\get_object_id;
use Makasim\Yadm\MongodbStorage;

class MongoProcessStorage implements ProcessStorage
{
    /**
     * @var MongodbStorage
     */
    private $storage;

    /**
     * @param MongodbStorage $storage
     */
    public function __construct(MongodbStorage $storage)
    {
        $this->storage = $storage;
    }

    public function persist(Process $process)
    {
        if (get_object_id($process)) {
            $this->storage->update($process);
        } else {
            $this->storage->insert($process);
        }
    }
}