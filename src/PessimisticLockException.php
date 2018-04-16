<?php
namespace Formapro\Pvm;

class PessimisticLockException extends \LogicException
{
    /**
     * @return PessimisticLockException
     */
    public static function lockFailed(\Throwable $previous)
    {
        return new self('The pessimistic lock failed.', null, $previous);
    }
}
