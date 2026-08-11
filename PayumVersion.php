<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle;

use Payum\Core\DI\ContainerConfiguration;

/**
 * Tells which generation of payum/core the bundle is running against.
 *
 * The bundle supports both, and wires itself differently for each, so this is asked in a handful of
 * places: which service configuration to load, how tagged services are handed to a gateway and which
 * core gateway factory to build.
 *
 * The installed version is not read from Composer's metadata on purpose. payum/payum replaces
 * payum/core, and neither declares a version outside of a release, so a development install reports a
 * version which says nothing about which APIs are there. The DI interfaces added in 2.0 do.
 */
final class PayumVersion
{
    /**
     * Whether payum/core builds its gateways with a dependency injection container, which is the case
     * from 2.0 onwards.
     */
    public static function supportsDependencyInjection(): bool
    {
        return interface_exists(ContainerConfiguration::class);
    }
}
