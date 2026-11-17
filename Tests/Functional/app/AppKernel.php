<?php

use Payum\Bundle\PayumBundle\Tests\Functional\app\AppKernelShared;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AppKernel extends AppKernelShared
{
    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        return parent::handle($request, $type, false);
    }
}
