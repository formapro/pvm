<?php
namespace Formapro\Pvm;


class UUID
{
    public static function generate()
    {
        return str_replace('.', '', uniqid('', true));
    }
}