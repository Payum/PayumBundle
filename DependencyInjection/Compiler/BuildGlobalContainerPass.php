<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\PayumVersion;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * Collects the services of the application Payum is allowed to see.
 *
 * They end up in a service locator handed to Payum as its global container, which means two things:
 * a gateway can ask for them, and they can be injected into the constructor of an action.
 *
 * Besides a handful of services of the framework, any service tagged payum.global_service is shared.
 * The tag takes an optional id attribute for the name Payum should know the service under, which is
 * how a service gets shared under the interface an action type-hints:
 *
 *     App\Payment\ExchangeRates:
 *         tags:
 *             - { name: payum.global_service, id: App\Payment\ExchangeRatesInterface }
 *
 * Only runs with payum/core 2.0 and later - 1.x has no notion of a global container.
 */
class BuildGlobalContainerPass implements CompilerPassInterface
{
    /**
     * Services of the framework which are shared when the application has them.
     */
    private const FRAMEWORK_SERVICES = [
        LoggerInterface::class => 'logger',
        UrlGeneratorInterface::class => 'router',
        EventDispatcherInterface::class => 'event_dispatcher',
        Environment::class => 'twig',
    ];

    public function process(ContainerBuilder $container): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            return;
        }

        if (! $container->hasDefinition('payum.di.global_container')) {
            return;
        }

        $services = [];

        foreach (self::FRAMEWORK_SERVICES as $id => $serviceId) {
            if ($container->has($serviceId)) {
                $services[$id] = new Reference($serviceId);
            }
        }

        foreach ($container->findTaggedServiceIds('payum.global_service') as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $services[$attributes['id'] ?? $serviceId] = new Reference($serviceId);
            }
        }

        $container->getDefinition('payum.di.global_container')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $services))
        ;
    }
}
