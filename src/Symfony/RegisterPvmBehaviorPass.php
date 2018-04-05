<?php
namespace Formapro\Pvm\Symfony;

use Formapro\Pvm\DefaultBehaviorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterPvmBehaviorPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $registryId;

    public function __construct($tag = 'pvm.behavior', $registryId = DefaultBehaviorRegistry::class)
    {
        $this->tag = $tag;
        $this->registryId = $registryId;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false == $container->hasDefinition($this->registryId)) {
            return;
        }

        $repository = $container->getDefinition($this->registryId);
        foreach ($container->findTaggedServiceIds($this->tag) as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $tagAttribute) {
                $behaviorName = empty($tagAttribute['behaviorName']) ? $serviceId : $tagAttribute['behaviorName'];

                $repository->addMethodCall('register', [$behaviorName, new Reference($serviceId)]);
            }
        }
    }
}