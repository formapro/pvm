# Mongo storage examples
 
## Install yadm lib

```bash
composer require makasim/yadm
```

## Store process

```php
<?php
namespace Acme;

use Formapro\Pvm\Process;
use Formapro\Pvm\Yadm\MongoProcessStorage;

$process = new Process();
$fooNode = $process->createNode();
$fooNode->setLabel('foo');
$fooNode->setBehavior('print_label');

$barNode = $process->createNode();
$barNode->setLabel('bar');
$barNode->setBehavior('print_label');

$process->createTransition($fooNode, $barNode);
$process->createTransition(null, $fooNode);

$client = new \MongoDB\Client();
$collection = $client->selectCollection('pvm', 'process');
$mongoStorage = new \Makasim\Yadm\Storage($collection, new \Makasim\Yadm\Hydrator(Process::class));
$processStorage = new MongoProcessStorage($mongoStorage);

$processStorage->persist($process);
```

## Persist storage in engine.

The execution will be persisted. It allows you to run tasks in parallel or pause the execution and continue later.

```php
<?php
namespace Acme;

use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\Yadm\MongoProcessStorage;
use Formapro\Pvm\Process;

$client = new \MongoDB\Client();
$collection = $client->selectCollection('pvm', 'process');
$mongoStorage = new \Makasim\Yadm\Storage($collection, new \Makasim\Yadm\Hydrator(Process::class));
$processStorage = new MongoProcessStorage($mongoStorage);

$engine = new ProcessEngine(new DefaultBehaviorRegistry(), $processStorage);

```

[Back](../README.md)

