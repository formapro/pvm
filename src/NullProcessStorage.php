<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessStorage;

class NullProcessStorage implements ProcessStorage
{
    /**
     * {@inheritdoc}
     */
    public function persist(Process $process)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        throw new \LogicException(sprintf('The process with id "%s" could not be found', $id));
    }
}