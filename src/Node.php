<?php
namespace Formapro\Pvm;

use Makasim\Values\ObjectsTrait;
use Makasim\Values\ValuesTrait;

class Node
{
    const SCHEMA = 'http://pvm.forma-pro.com/schemas/Node.json';

    use ValuesTrait {
        setValue as public;
        getValue as public;
    }

    use ObjectsTrait {
        setObject as public;
        getObject as public;
    }

    use CreateTrait;

    /**
     * @var Process
     */
    private $_process;

    public function __construct()
    {
        $this->setId(Uuid::generate());
    }

    /**
     * @return Process
     */
    public function getProcess()
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
    public function getLabel()
    {
        return $this->getValue('label', '');
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->setValue('label', $label);
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
    public function setBehavior($behavior)
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
