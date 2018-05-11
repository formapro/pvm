<?php
namespace Formapro\Pvm;

class TokenException extends \LogicException
{
    public static function notFound(string $id): self
    {
        return new static(sprintf('Token "%s" not found.', $id));
    }
}
