<?php

namespace Formapro\Pvm\Yadm;

use Formapro\Yadm\Index;
use Formapro\Yadm\StorageMetaInterface;

class ProcessStorageMeta implements StorageMetaInterface
{
    public function getIndexes(): array
    {
        return [
            new Index(['id' => 1], ['unique' => true]),
        ];
    }

    public function getCreateCollectionOptions(): array
    {
        return [];
    }

}