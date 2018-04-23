<?php
namespace Formapro\Pvm\Exception;

class WaitExecutionException extends \RuntimeException implements PvmException
{
    /**
     * @var bool
     */
    private $createWaitingTransition;

    public function __construct(bool $createWaitingTransition = true)
    {
        $this->createWaitingTransition = $createWaitingTransition;
    }

    /**
     * @return bool
     */
    public function isCreateWaitingTransition(): bool
    {
        return $this->createWaitingTransition;
    }
}
