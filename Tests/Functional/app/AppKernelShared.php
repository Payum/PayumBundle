<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\app;

use Payum;
use Symfony;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernelShared extends Kernel
{
    public function registerBundles(): array
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Payum\Bundle\PayumBundle\PayumBundle(),
        );

        return $bundles;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/PayumBundle/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/PayumBundle/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yml');

        $loader->load(__DIR__ . '/config/config_sf' . Kernel::MAJOR_VERSION . '.yml');
    }
}
