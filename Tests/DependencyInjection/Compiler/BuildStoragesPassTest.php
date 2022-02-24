<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewaysPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildStoragesPass;
use stdClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildStoragesPassTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementCompilerPassInterface(): void
    {
        $rc = new \ReflectionClass(BuildStoragesPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testShouldAddServiceWithTagToStaticRegistry(): void
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

    public function testThrowIfTagMissFactoryAttribute(): void
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage("The payum.storage tag require model_class attribute.");
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