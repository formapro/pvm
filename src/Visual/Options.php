<?php
namespace Formapro\Pvm\Visual;

use function Formapro\Values\get_value;
use function Formapro\Values\set_value;

class Options
{
    protected $values = [];

    /**
     * @return string
     */
    public function getType()
    {
        return get_value($this, 'type');
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        set_value($this, 'type', $type);
    }
}
