<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewaysPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildStoragesPass;
use stdClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildStoragesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementCompilerPassInterface()
    {
        $rc = new \ReflectionClass(BuildStoragesPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testShouldAddServiceWithTagToStaticRegistry()
    {
        $service = new Definition();
        $service->addTag('payum.storage', ['model_class' => stdClass::class]);

        $registry = new Definition(null, [null, null, null]);

        $container = new ContainerBuilder();
        $container->setDefinition('payum.static_registry', $registry);
        $container->setDefinition('aservice', $service);

        $pass = new BuildStoragesPass();

        $pass->process($container);

        $this->assertEquals(['stdClass' => 'aservice'], $registry->getArgument(1));
    }

    /**
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The payum.storage tag require model_class attribute.
     */
    public function testThrowIfTagMissFactoryAttribute()
    {
        $service = new Definition();
        $service->addTag('payum.storage');

        $registry = new Definition(null, [null, null, null]);

        $container = new ContainerBuilder();
        $container->setDefinition('payum.static_registry', $registry);
        $container->setDefinition('aservice', $service);

        $pass = new BuildStoragesPass();

        $pass->process($container);
    }
}