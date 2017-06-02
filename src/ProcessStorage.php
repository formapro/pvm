<?php
namespace Formapro\Pvm;


interface ProcessStorage
{
    /**
     * @param Process $process
     */
    public function persist(Process $process);

    /**
     * @param string $id
     *
     * @throw \LogicException if there is no such process
     *
     * @return Process
     */
    public function get($id);

}
