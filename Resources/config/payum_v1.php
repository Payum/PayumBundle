<?php

declare(strict_types=1);

use Payum\Bundle\PayumBundle\Builder\CoreGatewayFactoryBuilder;
use Payum\Bundle\PayumBundle\Builder\ObtainCreditCardActionBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The services which only make sense with payum/core 1.x.
 *
 * A gateway is configured there with an array whose values may be "@service" or "%parameter%" strings,
 * resolved at runtime by ContainerAwareCoreGatewayFactory, which is why both services below need the
 * container. See payum_v2.php for how the same thing is done from 2.0 onwards.
 */
return static function (ContainerBuilder $container): void {
    $container->register('payum.core_gateway_factory_builder', CoreGatewayFactoryBuilder::class)
        ->setPublic(false)
        ->setArguments([new Reference('service_container')])
    ;

    $container->register('payum.action.obtain_credit_card_builder', ObtainCreditCardActionBuilder::class)
        ->setPublic(true)
        ->setArguments([
            new Reference('form.factory'),
            new Reference('request_stack'),
        ])
    ;
};
