<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\PayumVersion;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers every service tagged payum.middleware on the builder, so it wraps every command on every
 * gateway. Services implementing Payum\Core\Middleware\MiddlewareInterface are tagged automatically, and
 * so are classes with #[AsPayumMiddleware], which also sets the priority.
 *
 * The tag takes an optional priority. Without one, the middleware's own HasPriority applies, else 0:
 *
 *     App\Payment\LogPaymentsMiddleware:
 *         tags:
 *             - { name: payum.middleware, priority: 1500 }
 *
 * Only runs with payum/core 2.0 and later - 1.x has no middleware.
 */
class BuildMiddlewaresPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (! PayumVersion::supportsDependencyInjection() || ! $container->hasDefinition('payum.builder')) {
            return;
        }

        $builder = $container->getDefinition('payum.builder');

        foreach ($container->findTaggedServiceIds('payum.middleware') as $serviceId => $tagAttributes) {
            // once per service: a class with #[AsPayumMiddleware] is also tagged for implementing
            // MiddlewareInterface, and must not wrap every command twice
            $priorities = array_filter(array_column($tagAttributes, 'priority'), static fn ($priority): bool => null !== $priority);

            $builder->addMethodCall('addMiddleware', [new Reference($serviceId), $priorities ? (int) reset($priorities) : null]);
        }
    }
}
