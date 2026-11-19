<?php

declare(strict_types=1);

use Payum\Bundle\PayumBundle\Builder\PayumCoreGatewayFactoryBuilder;
use Payum\Bundle\PayumBundle\DI\SymfonyContainerAdapter;
use Payum\Bundle\PayumBundle\Http\CurrentServerRequest;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The services which only make sense with payum/core 2.0 and later.
 *
 * From 2.0 on Payum builds every gateway with a dependency injection container of its own, on top of a
 * global one holding what all gateways share. The application is allowed to provide that global
 * container, which is what happens here: Payum looks a service up in the services of the application
 * first and falls back to its own defaults for everything else.
 *
 * The services shared this way are collected by BuildGlobalContainerPass, which fills in the locator
 * argument below once every bundle has had its say.
 */
return static function (ContainerBuilder $container): void {
    $container->register('payum.di.global_container', SymfonyContainerAdapter::class)
        ->setPublic(false)
        ->setArguments([
            null, // service locator - replaced while the container is built
        ])
    ;

    // the request Symfony is serving, shared with Payum by BuildGlobalContainerPass
    $container->register('payum.http.server_request', CurrentServerRequest::class)
        ->setPublic(false)
        ->setArguments([
            new Reference('request_stack'),
            // the application's own converter if it has one, else one finding the PSR-17 factories itself
            new Reference(HttpMessageFactoryInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
        ])
        ->addTag('kernel.reset', ['method' => 'reset'])
    ;

    $container->register('payum.core_gateway_factory_builder', PayumCoreGatewayFactoryBuilder::class)
        ->setPublic(false)
    ;

    $container->getDefinition('payum.builder')
        ->addMethodCall('setGlobalContainer', [new Reference('payum.di.global_container')])
    ;
};
