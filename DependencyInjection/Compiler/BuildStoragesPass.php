<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildStoragesPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('payum.static_registry');

        $servicesIds = [];
        foreach ($container->findTaggedServiceIds('payum.storage') as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (false == isset($attributes['model_class'])) {
                    throw new LogicException('The payum.storage tag require model_class attribute.');
                }

                $servicesIds[$attributes['model_class']] = $serviceId;
            }
        }

        $registry->replaceArgument(1, $servicesIds);
    }
}
