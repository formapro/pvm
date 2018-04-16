<?php
namespace Formapro\Pvm\Enqueue;

use Enqueue\Util\JSON;
use Formapro\Pvm\Token;
use Formapro\Pvm\Yadm\TokenException;

class HandleAsyncTransition implements \JsonSerializable
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $tokenTransitionId;

    public function __construct(string $token, string $tokenTransitionId)
    {
        $this->token = $token;
        $this->tokenTransitionId = $tokenTransitionId;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getTokenTransitionId(): string
    {
        return $this->tokenTransitionId;
    }

    public static function jsonUnserialize(string $json): self
    {
        $data = JSON::decode($json);

        if (false == array_key_exists('token', $data)) {
            throw new \InvalidArgumentException('The token key is missing');
        }

        if (false == array_key_exists('tokenTransitionId', $data)) {
            throw new \InvalidArgumentException('The tokenTransitionId key is missing');
        }

        return new static($data['token'], $data['tokenTransitionId']);
    }

    public function jsonSerialize(): array
    {
        return ['token' => $this->token, 'tokenTransitionId' => $this->tokenTransitionId];
    }

    public static function forToken(Token $token): self
    {
        return new static($token->getId(), $token->getCurrentTransition()->getId());
    }
}
