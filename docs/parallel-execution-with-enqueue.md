# Parallel execution with Enqueue
  
The tasks could be executed in parallel. 
To do that the process engine must be configured differently. 
Only transitions marked `async` are executed in parallel.
You must also provide a persisted storage as second argument. 
  
## Install Enqueue  
 
We need the `enqueue/simple-client` library and one of the transports, for example `entransitionp-ext`. More [here](https://github.com/php-enqueue/enqueue-dev/blob/master/docs/client/quick_tour.md)

## Process engine configuration
 
```php
<?php
// config.php

namespace Acme;

use Enqueue\SimpleClient\SimpleClient;
use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\CallbackBehavior;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\ProcessStorage;
use Formapro\Pvm\Process;
use Formapro\Pvm\Token;
use Formapro\Pvm\Enqueue\AsyncTransition;
use Formapro\Pvm\ObjectBuilderHook;

(new ObjectBuilderHook())->register();

$client = new SimpleClient('amqp://');
$asyncTransition = new AsyncTransition($client->getProducer());

/** @var ProcessStorage $persistentStorage */

$registry = new DefaultBehaviorRegistry();
$registry->register('print_label', new CallbackBehavior(function(Token $token) {
    echo $token->getTransition()->getTo()->getLabel().' ';
}));

$process = Process::create();
$foo = $process->createNode();
$foo->setLabel('foo');
$foo->setBehavior('print_label');

$bar = $process->createNode();
$bar->setLabel('bar');
$bar->setBehavior('print_label');

$baz = $process->createNode();
$baz->setLabel('baz');
$baz->setBehavior('print_label');

$process->createTransition($foo, $bar);

$transition = $process->createTransition($foo, $baz);
$transition->setAsync(true);

$firstTransition = $process->createTransition(null, $foo);

$engine = new ProcessEngine($registry, $persistentStorage, $asyncTransition);
```

## Execute process

```php
<?php
// main.php

namespace Acme;

use Formapro\Pvm\Process;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\Transition;

include __DIR__.'/config.php';

/** 
 * @var Process $process
 * @var ProcessEngine $engine
 * @var Transition $firstTransition 
 */

$token = $process->createToken($firstTransition);

$engine->proceed($token);
```

In the example above the `bar` task is executed in same process where `baz` is not, instead, the message is sent to the queue.
Then the message is picked up by a conusmer and gets processed.
Now, we have to configure a processor for the queue.
 
```php
<?php
// consume.php

namespace Acme;

use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\ProcessStorage;
use Enqueue\SimpleClient\SimpleClient;
use Formapro\Pvm\Enqueue\HandleAsyncTransitionProcessor;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrContext;
use Enqueue\Client\Config;

include __DIR__.'/config.php';

/** 
 * @var SimpleClient $client
 * @var ProcessEngine $engine
 * @var ProcessStorage $persistentStorage 
 */

$processor = new HandleAsyncTransitionProcessor($engine, $persistentStorage);

$client->bind(Config::COMMAND_TOPIC, HandleAsyncTransitionProcessor::COMMAND, $processor);

$client->consume();
```

[Back](../README.md)