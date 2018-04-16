<?php
namespace Formapro\Pvm\Enqueue;

use Enqueue\Consumption\Result;
use Formapro\Pvm\Token;

class HandleAsyncTransitionResult extends Result
{
    private $waitTokens = [];

    public function setWaitTokens(array $tokens): void
    {
        $this->waitTokens = $tokens;
    }

    /**
     * @return Token[]
     */
    public function getWaitTokens(): array
    {
        return $this->waitTokens;
    }
}
