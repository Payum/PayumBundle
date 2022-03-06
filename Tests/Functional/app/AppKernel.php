<?php

use Payum\Bundle\PayumBundle\PayumBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AppKernel extends Kernel
{
    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        return parent::handle($request, $type, false);
    }

    public function registerBundles(): array
    {
        $bundles = array(
            new FrameworkBundle(),
            new TwigBundle(),
            new PayumBundle(),
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
    }
}
