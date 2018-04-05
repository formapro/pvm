# Sequence example 

The example shows how you can execute tasks in a sequence.  

```php
<?php
namespace Acme;

use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\CallbackBehavior;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\Process;
use Formapro\Pvm\Token;
use Formapro\Pvm\Uuid;
use function Makasim\Values\register_object_hooks;

register_object_hooks();

$registry = new DefaultBehaviorRegistry();
$registry->register('print_label', new CallbackBehavior(function(Token $token) {
    echo $token->getCurrentTransition()->getTransition()->getTo()->getLabel().' ';
}));

$process = Process::create();
$process->setId(Uuid::generate());

$fooNode = $process->createNode();
$fooNode->setLabel('foo');
$fooNode->setBehavior('print_label');

$barNode = $process->createNode();
$barNode->setLabel('bar');
$barNode->setBehavior('print_label');

$process->createTransition($fooNode, $barNode);

$transition = $process->createTransition(null, $fooNode);

$engine = new ProcessEngine($registry);
$token = $engine->createProcessToken($process, $transition);
$engine->proceed($token);

// Prints "foo bar "
```

The diagram: 

![Example](images/sequence-example.png)

[Back](../README.md)

