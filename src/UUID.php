<?php
namespace Formapro\Pvm;


class UUID
{
    public static function generate()
    {
        return uniqid('', true);
    }
}