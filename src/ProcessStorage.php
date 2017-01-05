<?php
namespace Formapro\Pvm;


interface ProcessStorage
{
    /**
     * @param Process $process
     */
    public function persist(Process $process);
}
