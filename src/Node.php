<?php
namespace Formapro\Pvm;

use Makasim\Yadm\ValuesTrait;

class Node
{
    use ValuesTrait;

    /**
     * @var Process
     */
    private $_process;

    /**
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->_process;
    }

    /**
     * @param Process $process
     */
    public function setProcess(Process $process)
    {
        $this->_process = $process;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->setValue('id', $id);
    }

    /**
     * @return string
     */
    public function getBehavior()
    {
        return $this->getValue('behavior');
    }

    /**
     * @param string $behavior
     */
    public function setBehavior(string $behavior)
    {
        $this->setValue('behavior', $behavior);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setOption($key, $value)
    {
        $this->setValue('option.'.$key, $value);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->getValue('option.'.$key);
    }
}
