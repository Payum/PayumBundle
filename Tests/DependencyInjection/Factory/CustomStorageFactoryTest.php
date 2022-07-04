<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\CustomStorageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory;

class CustomStorageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorageFactory(): void
    {
        $rc = new \ReflectionClass(CustomStorageFactory::class);

        $this->assertTrue($rc->isSubclassOf(AbstractStorageFactory::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments(): void
    {
        $this->expectNotToPerformAssertions();
        new CustomStorageFactory();
    }

    /**
     * @test
     */
    public function shouldAllowGetName(): void
    {
        $factory = new CustomStorageFactory();

        $this->assertEquals('custom', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration(): void
    {
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'service' => 'service.name',
        )));

        $this->assertArrayHasKey('service', $config);
        $this->assertEquals('service.name', $config['service']);
    }

    /**
     * @test
     */
    public function shouldRequireServiceOption(): void
    {
        $this->expectExceptionMessageMatches("/The child (node|config) \"service\" (at path|under) \"foo\" must be configured\./");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }

    /**
     * @test
     */
    public function shouldNotAllowEmptyServiceOption(): void
    {
        $this->expectExceptionMessage("The path \"foo.service\" cannot contain an empty value, but got \"\".");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'service' => '',
        )));
    }

    /**
     * @test
     */
    public function shouldNotAllowNullServiceOption(): void
    {
        $this->expectExceptionMessage("The path \"foo.service\" cannot contain an empty value, but got null.");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'service' => null,
        )));
    }

    /**
     * @test
     */
    public function shouldAllowAddShortConfiguration(): void
    {
        $factory = new CustomStorageFactory;

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array('storageId'));

        $this->assertArrayHasKey('service', $config);
        $this->assertEquals('storageId', $config['service']);
    }

    /**
     * @test
     */
    public function shouldCreateServiceDefinition(): void
    {
        $serviceName = 'service.name';

        $containerBuilder = new ContainerBuilder();

        $factory = new CustomStorageFactory();
        $storageId = $factory->create($containerBuilder, 'stdClass', array('service' => $serviceName));

        $this->assertTrue($containerBuilder->hasDefinition($storageId));
        $this->assertSame($serviceName, $containerBuilder->getDefinition($storageId)->getParent());
    }

    /**
     * @return MockObject|\Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function createContainerBuilderMock()
    {
        return $this->createMock(ContainerBuilder::class);
    }
}
