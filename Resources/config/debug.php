<?php

declare(strict_types=1);

use Payum\Bundle\PayumBundle\Profiler\PayumCollector;
use Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerBuilder $container): void {
    $container->register('payum.extension.log_executed_actions', LogExecutedActionsExtension::class)
        ->setPublic(true)
        ->setArguments([new Reference('logger', ContainerBuilder::IGNORE_ON_INVALID_REFERENCE)])
        ->addTag('monolog.logger', ['channel' => 'payum'])
        ->addTag('payum.extension', ['all' => true, 'alias' => 'log_executed_actions'])
    ;

    $container->register('payum.profiler.payum_collector', PayumCollector::class)
        ->setPublic(true)
        ->addTag('payum.extension', ['all' => true, 'alias' => 'profile_collector', 'prepend' => true])
        ->addTag('data_collector', ['template' => '@Payum/Profiler/payum.html.twig', 'id' => 'payum'])
    ;
};
