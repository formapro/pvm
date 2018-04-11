<?php
namespace Formapro\Pvm;


interface ProcessStorage
{
    /**
     * @param Process $process
     */
    public function persist(Process $process): void;

    /**
     * @param string $id
     *
     * @throw \LogicException if there is no such process
     *
     * @return Process
     */
    public function get(string $id): Process;

    /**
     * @param string $tokenString
     *
     * @throw \LogicException if there is no such process
     *
     * @return Process
     */
    public function getByToken(string $tokenString): Process;
}
