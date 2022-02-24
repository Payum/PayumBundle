<?php

namespace Payum\Bundle\PayumBundle\Tests\Functional\Controller;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CancelControllerTest extends WebTestCase
{
    /**
     * @ticket 507
     */
    public function testCanBeAccessed(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('A token with hash `payum_token` could not be found.');

        $this->client->request('GET', '/payment/cancel/payum_token');
    }
}
