<?php
namespace Formapro\Pvm\Visual;

use Makasim\Yadm\ValuesTrait;

class Options
{
    use ValuesTrait;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getValue('type');
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->setValue('type', $type);
    }
}
