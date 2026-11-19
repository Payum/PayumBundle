<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Tells the profiler which gateways are registered, and with what: from the bundle configuration, by a
 * payum.gateway_config tag or as a payum.gateway service. Only the factory or config class, never an
 * option, as those hold the credentials.
 *
 * Runs after BuildGatewayConfigsPass, which adds the tagged configs to the builder.
 */
class BuildProfilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition('payum.profiler.payum_collector') || ! $container->hasDefinition('payum.builder')) {
            return;
        }

        $gateways = [];

        foreach ($container->getDefinition('payum.builder')->getMethodCalls() as [$method, $arguments]) {
            if ('addGateway' === $method) {
                $gateways[$arguments[0]] = ['registered_by' => 'factory', 'factory' => $arguments[1]['factory'] ?? null];
            }

            if ('registerGateway' === $method) {
                $gateways[$arguments[0]] = ['registered_by' => 'config', 'factory' => $this->classOf($container, $arguments[1])];
            }
        }

        foreach ($container->findTaggedServiceIds('payum.gateway') as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (isset($attributes['gateway'])) {
                    $gateways[$attributes['gateway']] = ['registered_by' => 'service', 'factory' => $serviceId];
                }
            }
        }

        ksort($gateways);

        $collector = $container->getDefinition('payum.profiler.payum_collector');
        $collector->replaceArgument(0, ['gateways' => $gateways] + $collector->getArgument(0));
    }

    private function classOf(ContainerBuilder $container, mixed $config): ?string
    {
        if ($config instanceof Reference) {
            $config = $container->findDefinition((string) $config);
        }

        return $config instanceof Definition ? $container->getParameterBag()->resolveValue($config->getClass()) : null;
    }
}
