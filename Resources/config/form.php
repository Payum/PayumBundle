<?php

declare(strict_types=1);

use Payum\Bundle\PayumBundle\Form\Extension\GatewayFactoriesChoiceTypeExtension;
use Payum\Bundle\PayumBundle\Form\Type\CreditCardExpirationDateType;
use Payum\Bundle\PayumBundle\Form\Type\CreditCardType;
use Payum\Bundle\PayumBundle\Form\Type\GatewayConfigType;
use Payum\Bundle\PayumBundle\Form\Type\GatewayFactoriesChoiceType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerBuilder $container): void {
    $container->setParameter('payum.available_gateway_factories', []);

    $container->register('payum.form.type.credit_card_expiration_date', CreditCardExpirationDateType::class)
        ->setPublic(true)
        ->addTag('form.type')
    ;

    $container->register('payum.form.type.credit_card', CreditCardType::class)
        ->setPublic(true)
        ->addTag('form.type')
    ;

    $container->register('payum.form.type.gateway_config', GatewayConfigType::class)
        ->setPublic(true)
        ->setArguments([new Reference('payum')])
        ->addTag('form.type')
    ;

    $container->register('payum.form.type.gateway_factories_choice', GatewayFactoriesChoiceType::class)
        ->setPublic(true)
        ->setArguments(['%payum.available_gateway_factories%'])
        ->addTag('form.type')
    ;

    $container->register('payum.form.extension.gateway_factories_choice', GatewayFactoriesChoiceTypeExtension::class)
        ->setPublic(true)
        ->setArguments([new Reference('payum')])
        ->addTag('form.type_extension', ['extended_type' => GatewayFactoriesChoiceType::class])
    ;
};
