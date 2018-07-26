<?php
namespace Formapro\Pvm;

use function Makasim\Values\get_value;
use function Makasim\Values\set_value;

class Node
{
    const SCHEMA = 'http://pvm.forma-pro.com/schemas/Node.json';

    protected $values = [];

    use CreateTrait;

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
    public function getId(): string
    {
        return get_value($this, 'id');
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        set_value($this, 'id', $id);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return get_value($this, 'label', '');
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label)
    {
        set_value($this, 'label', $label);
    }

    /**
     * @return string
     */
    public function getBehavior(): string
    {
        return get_value($this, 'behavior');
    }

    /**
     * @param string|null $behavior
     */
    public function setBehavior(string $behavior = null)
    {
        set_value($this, 'behavior', $behavior);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setOption(string $key, $value)
    {
        set_value($this, 'option.'.$key, $value);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getOption(string $key)
    {
        return get_value($this, 'option.'.$key);
    }
}
