<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildGatewayFactoriesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementCompilerPassInterface()
    {
        $rc = new \ReflectionClass(BuildConfigsPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testShouldAddServiceWithTagToStaticRegistry()
    {
        $service = new Definition();
        $service->addTag('payum.gateway_factory', ['factory' => 'foo']);

        $registry = new Definition(null, [null, null, null]);

        $container = new ContainerBuilder();
        $container->setDefinition('payum.static_registry', $registry);
        $container->setDefinition('aservice', $service);

        $pass = new BuildGatewayFactoriesPass();

        $pass->process($container);

        $this->assertEquals(['foo' => 'aservice'], $registry->getArgument(2));
    }

    /**
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The payum.gateway_factory tag require factory attribute.
     */
    public function testThrowIfTagMissFactoryAttribute()
    {
        $service = new Definition();
        $service->addTag('payum.gateway_factory');

        $registry = new Definition(null, [null, null, null]);

        $container = new ContainerBuilder();
        $container->setDefinition('payum.static_registry', $registry);
        $container->setDefinition('aservice', $service);

        $pass = new BuildGatewayFactoriesPass();

        $pass->process($container);
    }
}