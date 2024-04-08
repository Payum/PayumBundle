<?php

namespace Payum\Bundle\PayumBundle\Tests\Builder;

use Payum\Bundle\PayumBundle\Builder\CoreGatewayFactoryBuilder;
use Payum\Bundle\PayumBundle\ContainerAwareCoreGatewayFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class CoreGatewayFactoryBuilderTest extends TestCase
{
    public function testShouldImplementContainerAwareInterface(): void
    {
        $rc = new ReflectionClass(CoreGatewayFactoryBuilder::class);

        $this->assertTrue($rc->implementsInterface(ContainerAwareInterface::class));
    }

    public function testShouldBuildContainerAwareCoreGatewayFactory(): void
    {
        $container = new Container();
        $defaultConfig = [
            'foo' => 'fooVal',
        ];

        $builder = new CoreGatewayFactoryBuilder();
        $builder->setContainer($container);

        $gatewayFactory = $builder->build($defaultConfig);

        $this->assertInstanceOf(ContainerAwareCoreGatewayFactory::class, $gatewayFactory);
    }

    public function testAllowUseBuilderAsAsFunction(): void
    {
        $container = new Container();
        $defaultConfig = [
            'foo' => 'fooVal',
        ];

        $builder = new CoreGatewayFactoryBuilder();
        $builder->setContainer($container);

        $gatewayFactory = $builder($defaultConfig);

        $this->assertInstanceOf(ContainerAwareCoreGatewayFactory::class, $gatewayFactory);
    }
}
