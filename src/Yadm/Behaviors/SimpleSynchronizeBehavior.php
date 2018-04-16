<?php
namespace Formapro\Pvm\Yadm\Behaviors;

use Formapro\Pvm\Behavior;
use Formapro\Pvm\Exception\InterruptExecutionException;
use Formapro\Pvm\Process;
use Formapro\Pvm\Token;
use function Makasim\Values\get_value;
use Makasim\Yadm\Storage;
use MongoDB\Operation\FindOneAndUpdate;

class SimpleSynchronizeBehavior implements Behavior
{
    /**
     * @var Storage
     */
    private $processStorage;

    /**
     * @param Storage $processStorage
     */
    public function __construct(Storage $processStorage)
    {
        $this->processStorage = $processStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Token $token)
    {
        $process = $token->getProcess();
        $node = $token->getCurrentTransition()->getTransition()->getTo();

        $collection = $this->processStorage->getCollection();

        $rawRefreshedProcess = $collection->findOneAndUpdate(
            ['id' => new \Makasim\Yadm\Uuid($process->getId())],
            ['$inc' => ['nodes.'.$node->getId().'.currentWeight' => $token->getCurrentTransition()->getWeight()]],
            [
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
            ]
        );

        $refreshedProcess = Process::create($rawRefreshedProcess);
        $refreshedNode = $refreshedProcess->getNode($node->getId());

        if (get_value($refreshedNode, 'currentWeight') !== get_value($refreshedNode, 'requiredWeight')) {
            throw new InterruptExecutionException();
        }

        // continue execution.
    }
}
