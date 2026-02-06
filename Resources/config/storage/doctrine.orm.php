<?php

declare(strict_types=1);

use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerBuilder $container): void {
    $container->setParameter('payum.storage.doctrine.orm.class', DoctrineStorage::class);

    $container->register('payum.storage.doctrine.orm', '%payum.storage.doctrine.orm.class%')
        ->setPublic(false)
        ->setAbstract(true)
        ->setArguments([
            new Reference('payum.entity_manager'),
            null, // should be set in DoctrineStorageFactory
        ])
    ;

    $container->setAlias('payum.entity_manager', 'doctrine.orm.default_entity_manager')
        ->setPublic(false)
    ;
};
