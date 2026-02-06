<?php

declare(strict_types=1);

use Payum\Core\Storage\FilesystemStorage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('payum.storage.filesystem.class', FilesystemStorage::class)
    ;

    $services = $container->services();

    $services->set('payum.storage.filesystem.prototype', '%payum.storage.filesystem.class%')
        ->public(false)
        ->abstract()
        ->args([
            null, // should be set in FilesystemStorageFactory
            null, // should be set in FilesystemStorageFactory
            null, // should be set in FilesystemStorageFactory
        ])
    ;
};
