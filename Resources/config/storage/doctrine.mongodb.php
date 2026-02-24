<?php

declare(strict_types=1);

use Doctrine\ODM\MongoDB\DocumentManager;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('payum.storage.doctrine.mongodb.class', DoctrineStorage::class)
    ;

    $services = $container->services();

    $services->set('payum.storage.doctrine.mongodb', '%payum.storage.doctrine.mongodb.class%')
        ->public(false)
        ->abstract()
        ->args([
            new Reference('payum.document_manager'),
            null, // should be set in DoctrineStorageFactory
        ])
    ;

    $services->set('payum.document_manager', DocumentManager::class)
        ->public(false)
        ->factory([new Reference('doctrine_mongodb'), 'getManager'])
    ;
};
