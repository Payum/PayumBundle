<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AbstractStorageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementStorageFactoryInterface(): void
    {
        $rc = new \ReflectionClass(AbstractStorageFactory::class);

        $this->assertTrue($rc->implementsInterface(StorageFactoryInterface::class));
    }

    /**
     * @test
     */
    public function shouldBeAbstract(): void
    {
        $rc = new \ReflectionClass(StorageFactoryInterface::class);

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration(): void
    {
        $this->expectNotToPerformAssertions();

        $factory = $this->createAbstractStorageFactory();

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array());
    }

    /**
     * @test
     */
    public function shouldAllowCreateStorageAndReturnItsId(): void
    {
        $expectedStorage = new Definition();

        $factory = $this->createAbstractStorageFactory();
        $factory
            ->expects($this->once())
            ->method('createStorage')
            ->willReturnCallback(function () use ($expectedStorage) {
                return $expectedStorage;
            })
        ;

        $container = new ContainerBuilder;

        $actualStorageId = $factory->create($container, 'A\Model\Class', array());

        $this->assertEquals('payum.storage.a_model_class', $actualStorageId);
        $this->assertTrue($container->hasDefinition($actualStorageId));
        $this->assertSame($expectedStorage, $container->getDefinition($actualStorageId));
    }

    protected function assertDefinitionContainsMethodCall(Definition $serviceDefinition, $expectedMethod, $expectedFirstArgument): void
    {
        foreach ($serviceDefinition->getMethodCalls() as $methodCall) {
            if ($expectedMethod === $methodCall[0] && $expectedFirstArgument === $methodCall[1][0]) {
                return;
            }
        }

        $this->fail(sprintf(
            'Failed assert that service (Class: %s) has method %s been called with first argument %s',
            $serviceDefinition->getClass(),
            $expectedMethod,
            $expectedFirstArgument
        ));
    }

    /**
     * @return AbstractStorageFactory|MockObject
     */
    protected function createAbstractStorageFactory()
    {
        return $this->getMockForAbstractClass(AbstractStorageFactory::class);
    }
}
