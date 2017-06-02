<?php
namespace Formapro\Pvm;

class Uuid
{
    public static function generate()
    {
        return (string) \Ramsey\Uuid\Uuid::uuid4();
    }
}