<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesBuilderPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BuildGatewayFactoriesBuilderPassTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementCompilerPassInterface(): void
    {
        $rc = new \ReflectionClass(BuildGatewayFactoriesBuilderPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testShouldAddServiceWithTagToPayumBuilder(): void
    {
        $service = new Definition();
        $service->addTag('payum.gateway_factory_builder', ['factory' => 'fooFactory']);

        $builder = new Definition();

        $container = new ContainerBuilder();
        $container->setDefinition('payum.builder', $builder);
        $container->setDefinition('aservice', $service);

        $pass = new BuildGatewayFactoriesBuilderPass();

        $pass->process($container);

        $calls = $builder->getMethodCalls();
        $this->assertEquals('addGatewayFactory', $calls[0][0]);
        $this->assertEquals('fooFactory', (string) $calls[0][1][0]);
        $this->assertInstanceOf(Reference::class, $calls[0][1][1]);
        $this->assertEquals('aservice', (string) $calls[0][1][1]);
    }

    public function testThrowIfTagMissFactoryAttribute(): void
    {
        $this->expectExceptionMessage("The payum.gateway_factory tag require factory attribute.");
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $service = new Definition();
        $service->addTag('payum.gateway_factory_builder');

        $builder = new Definition();

        $container = new ContainerBuilder();
        $container->setDefinition('payum.builder', $builder);
        $container->setDefinition('aservice', $service);

        $pass = new BuildGatewayFactoriesBuilderPass();

        $pass->process($container);
    }
}