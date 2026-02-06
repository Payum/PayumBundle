<?php

declare(strict_types=1);

use Payum\Bundle\PayumBundle\Command\CreateCaptureTokenCommand;
use Payum\Bundle\PayumBundle\Command\CreateNotifyTokenCommand;
use Payum\Bundle\PayumBundle\Command\DebugGatewayCommand;
use Payum\Bundle\PayumBundle\Command\StatusCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerBuilder $container): void {
    $container->register('payum.command.create_capture_token', CreateCaptureTokenCommand::class)
        ->setArguments([new Reference('payum')])
        ->addTag('console.command')
    ;

    $container->register('payum.command.create_notify_token', CreateNotifyTokenCommand::class)
        ->setArguments([new Reference('payum')])
        ->addTag('console.command')
    ;

    $container->register('payum.command.debug_gateway', DebugGatewayCommand::class)
        ->setArguments([new Reference('payum')])
        ->addTag('console.command')
    ;

    $container->register('payum.command.status', StatusCommand::class)
        ->setArguments([new Reference('payum')])
        ->addTag('console.command')
    ;
};
