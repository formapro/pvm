<?php
namespace Formapro\Pvm;

class PvmClassMap
{
    /**
     * @var string[]
     */
    private $classMap;

    /**
     * @param string[] $classMap
     */
    public function __construct(array $classMap = [])
    {
        $this->classMap = array_replace([
            Process::SCHEMA => Process::class,
            Node::SCHEMA => Node::class,
            Token::SCHEMA => Token::class,
            Transition::SCHEMA => Transition::class,
        ], $classMap);
    }

    public function get(): array
    {
        return $this->classMap;
    }
}
