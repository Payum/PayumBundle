<?php

declare(strict_types=1);

use Payum\Bundle\PayumBundle\Action\GetHttpRequestAction;
use Payum\Bundle\PayumBundle\Builder\CoreGatewayFactoryBuilder;
use Payum\Bundle\PayumBundle\Builder\HttpRequestVerifierBuilder;
use Payum\Bundle\PayumBundle\Builder\ObtainCreditCardActionBuilder;
use Payum\Bundle\PayumBundle\Builder\TokenFactoryBuilder;
use Payum\Bundle\PayumBundle\ContainerAwareRegistry;
use Payum\Bundle\PayumBundle\EventListener\ReplyToHttpResponseListener;
use Payum\Bundle\PayumBundle\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Psr\Log\LoggerExtension;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerBuilder $container): void {
    $container->setParameter('payum.capture_path', 'payum_capture_do');
    $container->setParameter('payum.notify_path', 'payum_notify_do');
    $container->setParameter('payum.authorize_path', 'payum_authorize_do');
    $container->setParameter('payum.refund_path', 'payum_refund_do');
    $container->setParameter('payum.cancel_path', 'payum_cancel_do');
    $container->setParameter('payum.payout_path', 'payum_payout_do');

    $container->register('payum.builder', PayumBuilder::class)
        ->setPublic(false)
        ->addMethodCall('setMainRegistry', [new Reference('payum.static_registry')])
        ->addMethodCall('setHttpRequestVerifier', [new Reference('payum.http_request_verifier_builder')])
        ->addMethodCall('setTokenFactory', [new Reference('payum.token_factory_builder')])
        ->addMethodCall('setTokenStorage', [new Reference('payum.security.token_storage')])
        ->addMethodCall('setGenericTokenFactoryPaths', [[
            'capture' => '%payum.capture_path%',
            'notify' => '%payum.notify_path%',
            'authorize' => '%payum.authorize_path%',
            'refund' => '%payum.refund_path%',
            'cancel' => '%payum.cancel_path%',
            'payout' => '%payum.payout_path%',
        ]])
        ->addMethodCall('setCoreGatewayFactory', [new Reference('payum.core_gateway_factory_builder')])
    ;

    $container->register('payum', Payum::class)
        ->setPublic(true)
        ->setLazy(true)
        ->setFactory([new Reference('payum.builder'), 'getPayum'])
    ;

    $container->setAlias(Payum::class, 'payum')->setPublic(true);

    $container->register('payum.static_registry', ContainerAwareRegistry::class)
        ->setPublic(true)
        ->setArguments([
            [], // gateways services - replaced while container is built
            [], // storages services - replaced while container is built
            [], // gateways factories services - replaced while container is built
            new Reference('service_container'),
        ])
    ;

    $container->register('payum.converter.reply_to_http_response', ReplyToSymfonyResponseConverter::class)
        ->setPublic(true)
    ;

    $container->register('payum.listener.reply_to_http_response', ReplyToHttpResponseListener::class)
        ->setPublic(true)
        ->setArguments([new Reference('payum.converter.reply_to_http_response')])
        ->addTag('kernel.event_listener', ['event' => 'kernel.exception', 'method' => 'onKernelException', 'priority' => 128])
    ;

    $container->register('payum.security.token_storage')
        ->setAbstract(true)
    ;

    // Extensions
    $container->register('payum.extension.storage.prototype', StorageExtension::class)
        ->setAbstract(true)
        ->setPublic(false)
        ->setArguments([null]) // replaced with real storage service
    ;

    $container->register('payum.extension.logger', LoggerExtension::class)
        ->setPublic(true)
        ->setArguments([new Reference('logger', ContainerBuilder::IGNORE_ON_INVALID_REFERENCE)])
        ->addTag('monolog.logger', ['channel' => 'payum'])
        ->addTag('payum.extension', ['all' => true, 'alias' => 'psr_logger'])
    ;

    // Builders
    $container->register('payum.token_factory_builder', TokenFactoryBuilder::class)
        ->setPublic(false)
        ->setArguments([new Reference('router')])
    ;

    $container->register('payum.http_request_verifier_builder', HttpRequestVerifierBuilder::class)
        ->setPublic(false)
    ;

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

    $container->register('payum.action.get_http_request', GetHttpRequestAction::class)
        ->setPublic(true)
        ->addMethodCall('setHttpRequestStack', [new Reference('request_stack')])
    ;
};
