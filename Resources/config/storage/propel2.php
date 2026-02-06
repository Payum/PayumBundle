<?php

declare(strict_types=1);

use Payum\Core\Bridge\Propel2\Storage\Propel2Storage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('payum.storage.propel2', Propel2Storage::class)
        ->public(false)
        ->abstract()
        ->args([null]) // should be set in Propel2StorageFactory
    ;
};
