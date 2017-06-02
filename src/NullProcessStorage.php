<?php
namespace Formapro\Pvm;

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