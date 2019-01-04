<?php
namespace Formapro\Pvm;

use function Makasim\Values\build_object;

trait CreateTrait
{
    public static function create(array $data = [])
    {
        $classMap = [
            Process::SCHEMA => Process::class,
            Node::SCHEMA => Node::class,
            Token::SCHEMA => Token::class,
            Transition::SCHEMA => Transition::class,
            TokenTransition::SCHEMA => TokenTransition::class,
        ];

        return build_object($classMap[static::SCHEMA], array_replace([
            'schema' => static::SCHEMA,
        ], $data));
    }
}