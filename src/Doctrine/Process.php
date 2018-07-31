<?php
namespace Formapro\Pvm\Doctrine;

class Process
{
    private $id;

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

    public function setState(array $state)
    {
        $this->state = $state;
    }

    public function getState(): array
    {
        return $this->state;
    }
}
