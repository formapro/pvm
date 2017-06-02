<?php
namespace Formapro\Pvm\Enqueue;

use App\Async\Topics;
use Enqueue\Client\ProducerInterface;
use Formapro\Pvm\Token;

class AsyncTransition implements \Formapro\Pvm\AsyncTransition
{
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @param ProducerInterface $producer
     */
    public function __construct(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    /**
     * {@inheritdoc}
     */
    public function transition(array $tokens)
    {
        foreach ($tokens as $token) {
            /** @var Token $token */

            $this->producer->send(Topics::PVM_HANDLE_ASYNC_TRANSITION, [
                'process' => $token->getProcess()->getId(),
                'token' => $token->getId(),
            ]);
        }
    }
}