# Mongo storage examples

It takes [sequence example](sequence-example.md) and stores execution to [MongoDB](https://www.mongodb.com) store using [makasim/yadm](https://github.com/makasim/yadm).
 
## Install yadm lib

```bash
composer require makasim/yadm
```

## Store process

```php
<?php

namespace Acme;

use Formapro\Pvm\Process;
use Formapro\Pvm\Yadm\InProcessDAL;
use Makasim\Yadm\Hydrator;
use Makasim\Yadm\Storage;
use function Makasim\Values\register_object_hooks;

/** @var Process $process */

register_object_hooks();

$client = new \MongoDB\Client();
$collection = $client->selectCollection('pvm', 'process');
$processStorage = new Storage($collection, new Hydrator(Process::class));

$dal = new InProcessDAL($processStorage);

$dal->persistProcess($process);
```

## Persist storage in engine.

The execution will be persisted. It allows you to run tasks in parallel or pause the execution and continue later.

```php
<?php
namespace Acme;

use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\Transition;
use Formapro\Pvm\Yadm\InProcessDAL;
use Makasim\Yadm\Hydrator;
use Makasim\Yadm\Storage;
use function Makasim\Values\register_object_hooks;

// the process configuration remain the same, we have to adjust process engine only

/** @var DefaultBehaviorRegistry $registry */
/** @var Process $process */
/** @var Transition $transition */

register_object_hooks();

$client = new \MongoDB\Client();
$collection = $client->selectCollection('pvm', 'process');
$processStorage = new Storage($collection, new Hydrator(Process::class));

$dal = new InProcessDAL($processStorage);

$engine = new ProcessEngine($registry, $dal);
$token = $engine->createProcessToken($process, $transition);
$engine->proceed($token);
```

[Back](../README.md)

