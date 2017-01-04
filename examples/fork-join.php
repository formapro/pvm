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
use Formapro\Pvm\Transition;

include_once __DIR__.'/../vendor/autoload.php';

$process = new Process();

$fork = new Node();
$fork->setId('1');
$fork->setBehavior('fork');

$task1 = new Node();
$task1->setId('2');
$task1->setBehavior('echo');
$task1->setOption('text', 'task 1');

$task2 = new Node();
$task2->setId('3');
$task2->setBehavior('echo');
$task2->setOption('text', 'task 2');

$task3 = new Node();
$task3->setId('4');
$task3->setBehavior('echo');
$task3->setOption('text', 'task 3');

$join = new Node();
$join->setId('5');
$join->setBehavior('join');

$task4 = new Node();
$task4->setId('6');
$task4->setBehavior('echo');
$task4->setOption('text', 'task 4');

$process->addNode($fork);
$process->addNode($task1);
$process->addNode($task2);
$process->addNode($task3);
$process->addNode($task4);
$process->addNode($join);
$process->addTransition($start = new Transition(null, $fork));
$process->addTransition(new Transition($fork, $task1));
$process->addTransition(new Transition($fork, $task2));
$process->addTransition(new Transition($fork, $task3));
$process->addTransition(new Transition($task1, $join));
$process->addTransition(new Transition($task2, $join));
$process->addTransition(new Transition($task3, $join));
$process->addTransition(new Transition($join, $task4));

$behaviorRegistry = new DefaultBehaviorRegistry();
$behaviorRegistry->register('echo', new EchoBehavior());
$behaviorRegistry->register('fork', new CallbackBehavior(function (Token $token) {
    $transitions = $token->getProcess()->getOutTransitionsForNode($token->getTransition()->getTo());

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

$processStorage = new MongoProcessStorage();

$engine = new ProcessEngine($behaviorRegistry, $processStorage);
$engine->proceed($process->createToken($start));

