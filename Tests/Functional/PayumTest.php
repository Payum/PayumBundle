<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Core\Payum;

class PayumTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $payum = $this->container->get('payum');

        $this->assertInstanceOf(Payum::class, $payum);
    }
}