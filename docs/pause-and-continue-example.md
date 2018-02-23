# Pause and continue example 

The example shows how the process could be paused (for example you need extra info) and started later from the point where it was stopped.   

```php
<?php
namespace Acme;

use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\CallbackBehavior;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\Process;
use Formapro\Pvm\Token;
use Formapro\Pvm\Exception\WaitExecutionException;
use Formapro\Pvm\Uuid;
use function Makasim\Values\set_value;
use function Makasim\Values\get_value;

$registry = new DefaultBehaviorRegistry();
$registry->register('pause_and_continue', new CallbackBehavior(function(Token $token) {
    if (false == get_value($token, 'credit_card', false)) {
        echo 'need a card. ';
        throw new WaitExecutionException();
    }

    echo 'purchased. ';
}));

$process = Process::create();
$process->setId(Uuid::generate());

$fooNode = $process->createNode();
$fooNode->setLabel('foo');
$fooNode->setBehavior('pause_and_continue');

$transition = $process->createTransition(null, $fooNode);
$token = $process->createToken($transition);

$waitTokens = (new ProcessEngine($registry))->proceed($token);

// we asked a customer for a credit card. the processes has eneded.
// and now in another process we try to continue.
echo 'customer we need a card. ';
set_value($waitTokens[0], 'credit_card', '4111 1111 1111 1111');

echo 'card is here. ';
(new ProcessEngine($registry))->proceed($waitTokens[0]);

// Prints "need a card. customer we need a card. card is here. purchased."
```

[Back](../README.md)

