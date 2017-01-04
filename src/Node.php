<?php
namespace Formapro\Pvm;

use Makasim\Values\ValuesTrait;

class Node
{
    use ValuesTrait;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getSelfValue('id');
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->setSelfValue('id', $id);
    }

    /**
     * @return string
     */
    public function getBehavior()
    {
        return $this->getSelfValue('behavior');
    }

    /**
     * @param string $behavior
     */
    public function setBehavior(string $behavior)
    {
        $this->setSelfValue('behavior', $behavior);
    }

    public function setOption($key, $value)
    {
        $this->setValue('option', $key, $value);
    }

    public function getOption($key)
    {
        return $this->getValue('option', $key);
    }
}
