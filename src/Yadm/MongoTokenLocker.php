<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\PessimisticLockException;
use Formapro\Pvm\TokenLockerInterface;
use Makasim\Yadm\PessimisticLock;
use Makasim\Yadm\PessimisticLockException as YadmPessimisticLockException;

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
        try {
            $this->lock->lock($tokenId);
        } catch (YadmPessimisticLockException $e) {
            throw PessimisticLockException::lockFailed($e);
        }
    }

    public function unlock(string $tokenId): void
    {
        $this->lock->unlock($tokenId);
    }

    public function locked(string $tokenId): bool
    {
        return $this->lock->locked($tokenId);
    }
}
