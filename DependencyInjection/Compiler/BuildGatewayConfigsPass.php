<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers every service tagged `payum.gateway_config` as a gateway, under the tag's `gateway` attribute.
 */
class BuildGatewayConfigsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $tagged = $container->findTaggedServiceIds('payum.gateway_config');
        if ([] === $tagged) {
            return;
        }

        if (false === PayumVersion::supportsDependencyInjection()) {
            throw new LogicException('The payum.gateway_config tag needs payum/core 2.0 or later.');
        }

        $builder = $container->getDefinition('payum.builder');

        $registered = [];
        foreach ($builder->getMethodCalls() as [$method, $arguments]) {
            if ('registerGateway' === $method || 'addGateway' === $method) {
                $registered[$arguments[0]] = true;
            }
        }

        foreach ($tagged as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes['gateway'])) {
                    throw new LogicException(sprintf('The payum.gateway_config tag on service "%s" requires a gateway attribute.', $serviceId));
                }

                $gatewayName = $attributes['gateway'];
                if (isset($registered[$gatewayName])) {
                    throw new LogicException(sprintf('The gateway "%s" is registered twice: once by the payum.gateway_config tag on service "%s" and once elsewhere. Gateway names must be unique.', $gatewayName, $serviceId));
                }

                $registered[$gatewayName] = true;
                $builder->addMethodCall('registerGateway', [$gatewayName, new Reference($serviceId)]);
            }
        }
    }
}
