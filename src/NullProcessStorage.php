<?php
namespace Formapro\Pvm;

class NullProcessStorage implements ProcessStorage
{
    /**
     * {@inheritdoc}
     */
    public function persist(Process $process): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id): Process
    {
        throw new \LogicException(sprintf('The process with id "%s" could not be found', $id));
    }

    /**
     * {@inheritdoc}
     */
    public function getByToken(string $token): Process
    {
        throw new \LogicException(sprintf('The process that has token "%s" could not be found', $token));
    }
}