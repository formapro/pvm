<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\Token;
use Formapro\Pvm\TokenLockerInterface;
use Makasim\Yadm\PessimisticLock;

class MongoTokenLocker implements TokenLockerInterface
{
    /**
     * @var PessimisticLock
     */
    private $lock;

    public function __construct(PessimisticLock $lock)
    {
        $this->lock = $lock;
    }

    public function lock(Token $token): void
    {
        $this->lock->lock($token->getId());
    }

    public function unlock(Token $token): void
    {
        $this->lock->unlock($token->getId());
    }
}
