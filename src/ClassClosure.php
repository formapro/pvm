<?php
namespace Formapro\Pvm;

final class ClassClosure
{
    /**
     * @var ClassClosure
     */
    private static $instance;

    /**
     * @var PvmClassMap
     */
    private $classMap;

    private function __construct(PvmClassMap $classMap)
    {
        $this->classMap = $classMap;
    }

    public function __invoke(array $values): ?string
    {
        $classMap = $this->classMap->get();
        if (array_key_exists('schema', $values) && array_key_exists($values['schema'], $classMap)) {
            return $classMap[$values['schema']];
        }

        return null;
    }

    public static function create(): ClassClosure
    {
        if (false == self::$instance) {
            self::$instance = new ClassClosure(new PvmClassMap());
        }

        return self::$instance;
    }
}