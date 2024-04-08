<?php

namespace Payum\Bundle\PayumBundle\Tests\Builder;

use Payum\Bundle\PayumBundle\Builder\GatewayFactoryBuilder;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;

class GatewayFactoryBuilderTest extends TestCase
{
    public function testShouldBuildContainerAwareCoreGatewayFactory(): void
    {
        /** @var GatewayFactoryInterface $coreGatewayFactory */
        $coreGatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $defaultConfig = [
            'foo' => 'fooVal',
        ];

        $builder = new GatewayFactoryBuilder(GatewayFactory::class);

        $gatewayFactory = $builder->build($defaultConfig, $coreGatewayFactory);

        $this->assertInstanceOf(GatewayFactory::class, $gatewayFactory);
    }

    public function testAllowUseBuilderAsAsFunction(): void
    {
        /** @var GatewayFactoryInterface $coreGatewayFactory */
        $coreGatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $defaultConfig = [
            'foo' => 'fooVal',
        ];

        $builder = new GatewayFactoryBuilder(GatewayFactory::class);

        $gatewayFactory = $builder($defaultConfig, $coreGatewayFactory);

        $this->assertInstanceOf(GatewayFactory::class, $gatewayFactory);
    }
}
