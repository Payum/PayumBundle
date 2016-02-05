<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Bundle\PayumBundle\CoreGatewayFactory;
use Payum\Bundle\PayumBundle\GatewayFactory;

class CoreGatewayFactoryTest extends WebTestCase
{
    /**
     * @test
     */
    public function couldBeGetFromContainerAsService()
    {
        $factory = $this->container->get('payum.core_gateway_factory');

        $this->assertInstanceOf(CoreGatewayFactory::class, $factory);
    }

    /**
     * @test
     */
    public function couldBeGetFromContainerAsDeprecatedService()
    {
        $factory = $this->container->get('payum.gateway_factory');

        $this->assertInstanceOf(GatewayFactory::class, $factory);
    }
}