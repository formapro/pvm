<?php
namespace Formapro\Pvm;


interface ProcessStorage
{
    public function persist(Process $process);
}
