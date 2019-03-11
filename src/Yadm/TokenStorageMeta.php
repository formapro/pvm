<?php

namespace Formapro\Pvm\Yadm;

use Formapro\Yadm\Index;
use Formapro\Yadm\StorageMetaInterface;

class TokenStorageMeta implements StorageMetaInterface
{
    public function getIndexes(): array
    {
        return [
            new Index(['id' => 1], ['unique' => true]),
            new Index(['processId' => 1]),
        ];
    }

    public function getCreateCollectionOptions(): array
    {
        return [];
    }
}