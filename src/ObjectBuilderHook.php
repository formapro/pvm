<?php
namespace Formapro\Pvm;

use function Makasim\Values\register_global_hook;

class ObjectBuilderHook
{
    /**
     * @var array
     */
    private $classMap;

    /**
     * @param string[] $classMap
     */
    public function __construct(array $classMap = [])
    {
        $this->classMap = (new PvmClassMap($classMap))->get();
    }

    public function register()
    {
        register_global_hook('get_object_class', function(array $values) {
            if (isset($values['schema'])) {
                if (false == array_key_exists($values['schema'], $this->classMap)) {
                    throw new \LogicException(sprintf('An object has class set "%s" but there is no class for it', $values['class']));
                }

                return $this->classMap[$values['schema']];
            }
        });
    }
}
