# Process virtual machine

The library provides us with a frame to build a [workflow](https://en.wikipedia.org/wiki/Workflow), business processes such as [BPMN](http://www.bpmn.org/). 
It works like this: you build a process. It contains nodes and transitions between them. The process could be saved and used later. 
The process could be executed with process engine. The processes could be visualized.

It is backed up by [workflow nets (WF-nets)](https://en.wikipedia.org/wiki/Petri_net) mathematical model and [graphs theory](https://en.wikipedia.org/wiki/Graph_theory).
 
## The "Hello world!" example

Let's build our first process and execute it. It'll contain one single task that prints "Hello world" to the output.  

```php
<?php
namespace Acme;

use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\CallbackBehavior;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\Process;

$registry = new DefaultBehaviorRegistry();
$registry->register('hello_world', new CallbackBehavior(function() {
    echo 'Hello world';
}));

$process = new Process();
$node = $process->createNode();
$node->setLabel('Shouts "Hello world!"');
$node->setBehavior('hello_world');

$transition = $process->createTransition(null, $node);
$token = $process->createToken($transition);

(new ProcessEngine($registry))->proceed($token);
```

Here's the process diagram:

![Hello world graph](docs/images/hello_world_example.png)

## Developed by Forma-Pro

Forma-Pro is a full stack development company which interests also spread to open source development. 
Being a team of strong professionals we have an aim an ability to help community by developing cutting edge solutions in the areas of e-commerce, docker & microservice oriented architecture where we have accumulated a huge many-years experience. 
Our main specialization is Symfony framework based solution, but we are always looking to the technologies that allow us to do our job the best way. We are committed to creating solutions that revolutionize the way how things are developed in aspects of architecture & scalability.

If you have any questions and inquires about our open source development, this product particularly or any other matter feel free to contact at opensource@forma-pro.com
