<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Core\PayumBuilder;

class PayumBuilderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $builder = $this->container->get('payum.builder');

        $this->assertInstanceOf(PayumBuilder::class, $builder);
    }
}