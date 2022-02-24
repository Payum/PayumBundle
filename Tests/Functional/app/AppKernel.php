<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AppKernel extends Kernel
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

    public function handle(Request $request, int $type = HttpKernelInterface::MASTER_REQUEST, bool $catch = true): Response
    {
        /** @noinspection SuspiciousAssignmentsInspection */
        $catch = false;

        return parent::handle($request, $type, $catch);
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
