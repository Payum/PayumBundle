<?php

declare(strict_types=1);

use Payum\Bundle\PayumBundle\Controller\AuthorizeController;
use Payum\Bundle\PayumBundle\Controller\CancelController;
use Payum\Bundle\PayumBundle\Controller\CaptureController;
use Payum\Bundle\PayumBundle\Controller\NotifyController;
use Payum\Bundle\PayumBundle\Controller\PayoutController;
use Payum\Bundle\PayumBundle\Controller\PayumController;
use Payum\Bundle\PayumBundle\Controller\RefundController;
use Payum\Bundle\PayumBundle\Controller\SyncController;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerBuilder $container): void {
    $container->register(PayumController::class)
        ->setAbstract(true)
        ->setArguments([new Reference('payum')])
    ;

    $container->setDefinition(AuthorizeController::class, new ChildDefinition(PayumController::class))
        ->setPublic(true)
        ->setAutowired(true)
        ->addTag('container.service_subscriber')
    ;

    $container->setDefinition(CancelController::class, new ChildDefinition(PayumController::class))
        ->setPublic(true)
        ->setAutowired(true)
        ->addTag('container.service_subscriber')
    ;

    $container->setDefinition(CaptureController::class, new ChildDefinition(PayumController::class))
        ->setPublic(true)
        ->setAutowired(true)
        ->addTag('container.service_subscriber')
    ;

    $container->setDefinition(NotifyController::class, new ChildDefinition(PayumController::class))
        ->setPublic(true)
        ->setAutowired(true)
        ->addTag('container.service_subscriber')
    ;

    $container->setDefinition(PayoutController::class, new ChildDefinition(PayumController::class))
        ->setPublic(true)
        ->setAutowired(true)
        ->addTag('container.service_subscriber')
    ;

    $container->setDefinition(RefundController::class, new ChildDefinition(PayumController::class))
        ->setPublic(true)
        ->setAutowired(true)
        ->addTag('container.service_subscriber')
    ;

    $container->setDefinition(SyncController::class, new ChildDefinition(PayumController::class))
        ->setPublic(true)
        ->setAutowired(true)
        ->addTag('container.service_subscriber')
    ;
};
