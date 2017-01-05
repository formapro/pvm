<?php

use Formapro\Pvm\CallbackBehavior;
use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\EchoBehavior;
use Formapro\Pvm\Exception\InterruptExecutionException;
use Formapro\Pvm\MongoProcessStorage;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\Token;
use Formapro\Pvm\Node;
use Formapro\Pvm\Process;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

include_once __DIR__.'/../vendor/autoload.php';

$process = new Process();

$fork = new Node();
$fork->setId('fork');
$fork->setBehavior('fork');

$task1 = new Node();
$task1->setId('task 1');
$task1->setBehavior('echo');
$task1->setOption('text', 'task 1');

$task2 = new Node();
$task2->setId('task 2');
$task2->setBehavior('echo');
$task2->setOption('text', 'task 2');

$task3 = new Node();
$task3->setId('task 3');
$task3->setBehavior('echo');
$task3->setOption('text', 'task 3');

$join = new Node();
$join->setId('join');
$join->setBehavior('join');

$task4 = new Node();
$task4->setId('task 4');
$task4->setBehavior('echo');
$task4->setOption('text', 'task 4');

$process->addNode($fork);
$process->addNode($task1);
$process->addNode($task2);
$process->addNode($task3);
$process->addNode($task4);
$process->addNode($join);
$start = $process->createTransition(null, $fork);
$process->createTransition($fork, $task1);
$process->createTransition($fork, $task2);
$process->createTransition($fork, $task3);
$process->createTransition($task1, $join);
$process->createTransition($task2, $join);
$process->createTransition($task3, $join);
$process->createTransition($join, $task4);

$behaviorRegistry = new DefaultBehaviorRegistry();
$behaviorRegistry->register('echo', new EchoBehavior());
$behaviorRegistry->register('fork', new CallbackBehavior(function (Token $token) {
    $transitions = $token->getProcess()->getOutTransitions($token->getTransition()->getTo());

    $transitions[0]->setWeight(1);
    $transitions[1]->setWeight(1);
    $transitions[2]->setWeight(1);

    return $transitions;
}));
$behaviorRegistry->register('join', new CallbackBehavior(function (Token $token) {
    static $weight = 0;
    $weight += $token->getTransition()->getWeight();

    if ($weight === 3) {
        return;
    }

    throw new InterruptExecutionException();
}));

$client = new \MongoDB\Client();
$collection = $client->selectCollection('pvm', 'process');
$mongoStorage = new \Makasim\Yadm\MongodbStorage($collection, new \Makasim\Yadm\Hydrator(Process::class));
$processStorage = new MongoProcessStorage($mongoStorage);

$logger = new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG));

$engine = new ProcessEngine($behaviorRegistry, $processStorage);
$engine->proceed($process->createToken($start), $logger);

