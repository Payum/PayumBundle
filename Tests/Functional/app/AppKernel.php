<?php

use Payum\Bundle\PayumBundle\PayumBundle;
use Payum\Bundle\PayumBundle\Tests\Functional\app\AppKernelShared;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

if (Kernel::MAJOR_VERSION === 4) {
    class AppKernel extends AppKernelShared
    {
        public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true): Response
        {
            return parent::handle($request, $type, false);
        }
    }
} else {
    class AppKernel extends AppKernelShared
    {
        public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
        {
            return parent::handle($request, $type, false);
        }
    }
}
