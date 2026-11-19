<?php

declare(strict_types=1);

use Payum\Bundle\PayumBundle\Profiler\PayumCollector;
use Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension;
use Payum\Core\Event\CommandDispatched;
use Payum\Core\Event\CommandFailed;
use Payum\Core\Event\FailureRaised;
use Payum\Core\Event\HandlerResolved;
use Payum\Core\Event\IdempotentResultReplayed;
use Payum\Core\Event\ResultReturned;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Event\WebhookReceived;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerBuilder $container): void {
    $container->register('payum.extension.log_executed_actions', LogExecutedActionsExtension::class)
        ->setPublic(true)
        ->setArguments([new Reference('logger', ContainerBuilder::IGNORE_ON_INVALID_REFERENCE)])
        ->addTag('monolog.logger', ['channel' => 'payum'])
        ->addTag('payum.extension', ['all' => true, 'alias' => 'log_executed_actions'])
    ;

    $collector = $container->register('payum.profiler.payum_collector', PayumCollector::class)
        ->setPublic(true)
        ->setArguments([
            [], // summary of the configuration - set by PayumExtension and BuildProfilerPass
            // lazily: the gateways are only looked up for what the profiler shows of them
            new ServiceClosureArgument(new Reference('payum')),
            new ServiceClosureArgument(new Reference('payum.dynamic_gateways.config_storage', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)),
            new Reference('debug.stopwatch', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
        ])
        ->addTag('payum.extension', ['all' => true, 'alias' => 'profile_collector', 'prepend' => true])
        ->addTag('data_collector', ['template' => '@Payum/Profiler/payum.html.twig', 'id' => 'payum'])
    ;

    // payum/core 2.0 dispatches these through the application's event dispatcher
    if (class_exists(CommandDispatched::class)) {
        $listeners = [
            CommandDispatched::class => 'onCommandDispatched',
            HandlerResolved::class => 'onHandlerResolved',
            IdempotentResultReplayed::class => 'onIdempotentResultReplayed',
            WebhookReceived::class => 'onWebhookReceived',
            StatusChanged::class => 'onStatusChanged',
            ResultReturned::class => 'onResultReturned',
            FailureRaised::class => 'onFailureRaised',
            CommandFailed::class => 'onCommandFailed',
        ];

        foreach ($listeners as $event => $method) {
            $collector->addTag('kernel.event_listener', ['event' => $event, 'method' => $method]);
        }
    }
};
