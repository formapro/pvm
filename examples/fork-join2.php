<?php

use Formapro\Pvm\CallbackBehavior;
use Formapro\Pvm\DefaultBehaviorRegistry;
use Formapro\Pvm\EchoBehavior;
use Formapro\Pvm\Exception\InterruptExecutionException;
use Formapro\Pvm\Yadm\MongoProcessStorage;
use Formapro\Pvm\ProcessEngine;
use Formapro\Pvm\Token;
use Formapro\Pvm\Process;
use Formapro\Pvm\Visual\GraphVizVisual;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

include_once __DIR__.'/../vendor/autoload.php';

$process = new Process();

$fork1 = $process->createNode();
$fork1->setLabel('fork1');
$fork1->setBehavior('fork1');
$fork1->setValue('visual.type', 'gateway');

$fork2 = $process->createNode();
$fork2->setLabel('fork2');
$fork2->setBehavior('fork2');
$fork2->setValue('visual.type', 'gateway');

$task1 = $process->createNode();
$task1->setLabel('task1');
$task1->setBehavior('echo');
$task1->setOption('text', 'task1');

$task2 = $process->createNode();
$task2->setLabel('task2');
$task2->setBehavior('echo');
$task2->setOption('text', 'task2');

$task3 = $process->createNode();
$task3->setLabel('task3');
$task3->setBehavior('echo');
$task3->setOption('text', 'task3');

$task4 = $process->createNode();
$task4->setLabel('task4');
$task4->setBehavior('echo');
$task4->setOption('text', 'task4');

$task5 = $process->createNode();
$task5->setLabel('task5');
$task5->setBehavior('echo');
$task5->setOption('text', 'task5');

$join = $process->createNode();
$join->setLabel('join');
$join->setBehavior('join');
$join->setValue('visual.type', 'gateway');

$start = $process->createTransition(null, $fork1);
$process->createTransition($fork1, $task1);
$process->createTransition($fork1, $task2);
$process->createTransition($task1, $fork2);
$process->createTransition($fork2, $task3);
$process->createTransition($fork2, $task4);
$process->createTransition($task3, $join);
$process->createTransition($task4, $join);
$process->createTransition($task2, $join);
$process->createTransition($join, $task5);

$behaviorRegistry = new DefaultBehaviorRegistry();
$behaviorRegistry->register('echo', new EchoBehavior());
$behaviorRegistry->register('fork1', new CallbackBehavior(function (Token $token) {
    $transitions = $token->getProcess()->getOutTransitions($token->getTransition()->getTo());

    $transitions[0]->setWeight(1);
    $transitions[1]->setWeight(1);

    return $transitions;
}));
$behaviorRegistry->register('fork2', new CallbackBehavior(function (Token $token) {
    $transitions = $token->getProcess()->getOutTransitions($token->getTransition()->getTo());

    $transitions[0]->setWeight(2);
//    $transitions[1]->setWeight(1);

    return [$transitions[0]];
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
$mongoStorage = new \Makasim\Yadm\Storage($collection, new \Makasim\Yadm\Hydrator(Process::class));
$processStorage = new MongoProcessStorage($mongoStorage);

$logger = new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG));

$engine = new ProcessEngine($behaviorRegistry, $processStorage);
$engine->proceed($process->createToken($start), $logger);

$graphViz = new GraphVizVisual();
$graphViz->display($process);
