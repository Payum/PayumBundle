<?php

declare(strict_types=1);

use Payum\Core\Bridge\Propel\Storage\Propel1Storage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('payum.storage.propel1', Propel1Storage::class)
        ->public(false)
        ->abstract()
        ->args([null]) // should be set in Propel1StorageFactory
    ;
};
