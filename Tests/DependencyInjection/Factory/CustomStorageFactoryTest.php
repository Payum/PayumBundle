<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory;


use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\CustomStorageFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomStorageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorageFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\CustomStorageFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CustomStorageFactory();
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new CustomStorageFactory();

        $this->assertEquals('custom', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
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
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp  /The child (node|config) "service" (at path|under) "foo" must be configured\./
     */
    public function shouldRequireServiceOption()
    {
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The path "foo.service" cannot contain an empty value, but got "".
     */
    public function shouldNotAllowEmptyServiceOption()
    {
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
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The path "foo.service" cannot contain an empty value, but got null.
     */
    public function shouldNotAllowNullServiceOption()
    {
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
    public function shouldAllowAddShortConfiguration()
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
    public function shouldCreateServiceDefinition()
    {
        $serviceName = 'service.name';

        $containerBuilder = new ContainerBuilder();

        $factory = new CustomStorageFactory();
        $storageId = $factory->create($containerBuilder, 'stdClass', array('service' => $serviceName));

        $this->assertTrue($containerBuilder->hasDefinition($storageId));
        $this->assertSame($serviceName, $containerBuilder->getDefinition($storageId)->getParent());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function createContainerBuilderMock()
    {
        return $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder', array(), array(), '', false);
    }
}
