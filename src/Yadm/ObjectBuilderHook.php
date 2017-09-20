<?php
namespace Formapro\Pvm\Yadm;

use Formapro\Pvm\Node;
use Formapro\Pvm\Process;
use Formapro\Pvm\Token;
use Formapro\Pvm\Transition;
use function Makasim\Values\register_global_hook;

class ObjectBuilderHook
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
            Process::class => Process::class,
            Node::class => Node::class,
            Token::class => Token::class,
            Transition::class => Transition::class,
        ], $classMap);
    }

    public function register()
    {
        register_global_hook('get_object_class', function(array $values) {
            if (isset($values['class'])) {
                if (false == array_key_exists($values['class'], $this->classMap)) {
                    throw new \LogicException(sprintf('An object has class set "%s" but there is no class for it', $values['class']));
                }

                return $this->classMap[$values['class']];
            }
        });
    }
}
