<?php

namespace Payum\Bundle\PayumBundle\Tests\Builder;

use Payum\Bundle\PayumBundle\Builder\CoreGatewayFactoryBuilder;
use Payum\Bundle\PayumBundle\ContainerAwareCoreGatewayFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class CoreGatewayFactoryBuilderTest extends TestCase
{
    public function testShouldBuildContainerAwareCoreGatewayFactory(): void
    {
        $container = new Container();
        $defaultConfig = [
            'foo' => 'fooVal',
        ];

        $builder = new CoreGatewayFactoryBuilder($container);

        $gatewayFactory = $builder->build($defaultConfig);

        $this->assertInstanceOf(ContainerAwareCoreGatewayFactory::class, $gatewayFactory);
    }

    public function testAllowUseBuilderAsAsFunction(): void
    {
        $container = new Container();
        $defaultConfig = [
            'foo' => 'fooVal',
        ];

        $builder = new CoreGatewayFactoryBuilder($container);

        $gatewayFactory = $builder($defaultConfig);

        $this->assertInstanceOf(ContainerAwareCoreGatewayFactory::class, $gatewayFactory);
    }
}
