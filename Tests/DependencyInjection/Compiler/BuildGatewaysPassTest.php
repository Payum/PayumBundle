<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewaysPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildGatewaysPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementCompilerPassInterface()
    {
        $rc = new \ReflectionClass(BuildGatewaysPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testShouldAddServiceWithTagToStaticRegistry()
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

    /**
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The payum.gateway tag require gateway attribute.
     */
    public function testThrowIfTagMissFactoryAttribute()
    {
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