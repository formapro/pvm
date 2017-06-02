<?php
namespace Formapro\Pvm;


class UUID
{
    public static function generate()
    {
        return (string) \Ramsey\Uuid\Uuid::uuid4();
    }
}