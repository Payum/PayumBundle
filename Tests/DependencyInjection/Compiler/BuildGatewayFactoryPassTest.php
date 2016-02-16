<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoryPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BuildGatewayFactoryPassTest extends \Phpunit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsCompilerPassInteface()
    {
        $rc = new \ReflectionClass(BuildGatewayFactoryPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new BuildGatewayFactoryPass();
    }
        
//    /**
//     * @test
//     *
//     * @expectedException \Payum\Core\Exception\LogicException
//     * @expectedExceptionMessage The payum.gateway_factory tag require factory_name attribute.
//     */
//    public function throwsIfGatewayFactoryServiceTaggedButMissFactoryNameAttribute()
//    {
//        $gatewayFactory = new Definition();
//        $gatewayFactory->addTag('payum.gateway_factory', []);
//
//        $builder = new Definition();
//
//        $coreGatewayFactory = new Definition();
//        $coreGatewayFactory->addArgument('foo');
//        $coreGatewayFactory->addArgument('bar');
//        $coreGatewayFactory->addArgument('baz');
//
//        $container = new ContainerBuilder;
//        $container->setDefinition('foo_factory', $gatewayFactory);
//        $container->setDefinition('payum.builder', $builder);
//        $container->setDefinition('payum.core_gateway_factory', $coreGatewayFactory);
//
//        $pass = new BuildGatewayFactoryPass;
//
//        $pass->process($container);
//    }
//
//    /**
//     * @test
//     */
//    public function shouldAddGatewayFactoryToBuilder()
//    {
//        $gatewayFactory = new Definition();
//        $gatewayFactory->addTag('payum.gateway_factory', ['factory_name' => 'foo']);
//
//        $builder = new Definition();
//
//        $coreGatewayFactory = new Definition();
//        $coreGatewayFactory->addArgument('foo');
//        $coreGatewayFactory->addArgument('bar');
//        $coreGatewayFactory->addArgument('baz');
//
//        $container = new ContainerBuilder;
//        $container->setDefinition('payum.core_gateway_factory', $coreGatewayFactory);
//        $container->setDefinition('foo_factory', $gatewayFactory);
//        $container->setDefinition('payum.builder', $builder);
//
//        $pass = new BuildGatewayFactoryPass;
//
//        $pass->process($container);
//
//        $calls = $builder->getMethodCalls();
//        $this->assertCount(1, $calls);
//
//        $this->assertEquals('addGatewayFactory', $calls[0][0]);
//        $this->assertEquals('foo', $calls[0][1][0]);
//        $this->assertInstanceOf(Reference::class, $calls[0][1][1]);
//        $this->assertEquals('foo_factory', $calls[0][1][1]);
//    }
//
//    /**
//     * @test
//     */
//    public function shouldAddSeveralGatewayFactoriesToBuilder()
//    {
//        $container = new ContainerBuilder;
//
//        $gatewayFactory = new Definition();
//        $gatewayFactory->addTag('payum.gateway_factory', ['factory_name' => 'foo']);
//        $container->setDefinition('foo_factory', $gatewayFactory);
//
//        $gatewayFactory = new Definition();
//        $gatewayFactory->addTag('payum.gateway_factory', ['factory_name' => 'bar']);
//        $container->setDefinition('bar_factory', $gatewayFactory);
//
//        $gatewayFactory = new Definition();
//        $gatewayFactory->addTag('payum.gateway_factory', ['factory_name' => 'baz']);
//        $container->setDefinition('baz_factory', $gatewayFactory);
//
//        $builder = new Definition();
//
//        $coreGatewayFactory = new Definition();
//        $coreGatewayFactory->addArgument('foo');
//        $coreGatewayFactory->addArgument('bar');
//        $coreGatewayFactory->addArgument('baz');
//
//        $container->setDefinition('payum.core_gateway_factory', $coreGatewayFactory);
//        $container->setDefinition('payum.builder', $builder);
//
//        $pass = new BuildGatewayFactoryPass;
//
//        $pass->process($container);
//
//        $calls = $builder->getMethodCalls();
//        $this->assertCount(3, $calls);
//    }
}
