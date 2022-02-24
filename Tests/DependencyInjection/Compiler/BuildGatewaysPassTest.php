<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewaysPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildGatewaysPassTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementCompilerPassInterface(): void
    {
        $rc = new \ReflectionClass(BuildGatewaysPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testShouldAddServiceWithTagToStaticRegistry(): void
    {
        $service = new Definition();
        $service->addTag('payum.gateway', ['gateway' => 'foo']);

        $registry = new Definition(null, [null, null, null]);

        $container = new ContainerBuilder();
        $container->setDefinition('payum.static_registry', $registry);
        $container->setDefinition('aservice', $service);

        $pass = new BuildGatewaysPass();

        $pass->process($container);

        $this->assertEquals(['foo' => 'aservice'], $registry->getArgument(0));
    }

    public function testThrowIfTagMissFactoryAttribute(): void
    {
        $this->expectExceptionMessage("The payum.gateway tag require gateway attribute.");
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $service = new Definition();
        $service->addTag('payum.gateway');

        $registry = new Definition(null, [null, null, null]);

        $container = new ContainerBuilder();
        $container->setDefinition('payum.static_registry', $registry);
        $container->setDefinition('aservice', $service);

        $pass = new BuildGatewaysPass();

        $pass->process($container);
    }
}