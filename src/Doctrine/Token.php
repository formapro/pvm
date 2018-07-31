<?php
namespace Formapro\Pvm\Doctrine;

class Token
{
    private $id;

    private $processId;

    private $state;

    public function __construct()
    {
        $this->state = [];
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setProcessId(string $processId)
    {
        $this->processId = $processId;
    }

    public function getProcessId()
    {
        return $this->processId;
    }

    public function setState(array $state)
    {
        $this->state = $state;
    }

    public function getState(): array
    {
        return $this->state;
    }
}
