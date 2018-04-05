<?php
namespace Formapro\Pvm\Yadm;

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

    public function lock(string $tokenId): void
    {
        $this->lock->lock($tokenId);
    }

    public function unlock(string $tokenId): void
    {
        $this->lock->unlock($tokenId);
    }
}
