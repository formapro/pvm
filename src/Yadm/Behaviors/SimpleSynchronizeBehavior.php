<?php
namespace Formapro\Pvm\Yadm\Behaviors;

use App\Model\Process;
use Formapro\Pvm\Behavior;
use Formapro\Pvm\Exception\InterruptExecutionException;
use Formapro\Pvm\Token;
use Formapro\Pvm\Yadm\MongoProcessStorage;
use function Makasim\Values\build_object;
use MongoDB\Operation\FindOneAndUpdate;

class SimpleSynchronizeBehavior implements Behavior
{
    /**
     * @var MongoProcessStorage
     */
    private $processExecutionStorage;

    /**
     * @param MongoProcessStorage $processExecutionStorage
     */
    public function __construct(MongoProcessStorage $processExecutionStorage)
    {
        $this->processExecutionStorage = $processExecutionStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Token $token)
    {
        $process = $token->getProcess();
        $node = $token->getTransition()->getTo();

        $collection = $this->processExecutionStorage->getStorage()->getCollection();

        $rawRefreshedProcess = $collection->findOneAndUpdate(
            ['id' => $process->getId()],
            ['$inc' => ['nodes.'.$node->getId().'.currentWeight' => $token->getTransition()->getWeight()]],
            [
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
            ]
        );

        /** @var Process $refreshedProcess */
        $refreshedProcess = build_object(Process::class, $rawRefreshedProcess);
        $refreshedNode = $refreshedProcess->getNode($node->getId());

        if ($refreshedNode->getValue('currentWeight') !== $refreshedNode->getValue('requiredWeight')) {
            throw new InterruptExecutionException();
        }

        // continue execution.
    }
}
