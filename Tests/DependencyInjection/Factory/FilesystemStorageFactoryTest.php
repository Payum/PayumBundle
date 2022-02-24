<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class FilesystemStorageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorageFactory(): void
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments(): void
    {
        $this->expectNotToPerformAssertions();
        new FilesystemStorageFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName(): void
    {
        $factory = new FilesystemStorageFactory;

        $this->assertEquals('filesystem', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration(): void
    {
        $factory = new FilesystemStorageFactory;

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'id_property' => 'id',
            'storage_dir' => '/the/path/to/store/models',
        )));

        $this->assertArrayHasKey('id_property', $config);
        $this->assertEquals('id', $config['id_property']);

        $this->assertArrayHasKey('storage_dir', $config);
        $this->assertEquals('/the/path/to/store/models', $config['storage_dir']);
    }

    /**
     * @test
     */
    public function shouldRequireStorageDirOption(): void
    {
        $this->expectExceptionMessageMatches("/The child (node|config) \"storage_dir\" (at path|under) \"foo\" must be configured\./");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $factory = new FilesystemStorageFactory;

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }

    /**
     * @test
     */
    public function shouldSetIdPropertyToNull(): void
    {
        $factory = new FilesystemStorageFactory;

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'storage_dir' => '/the/path/to/store/models',
        )));

        $this->assertArrayHasKey('id_property', $config);
        $this->assertNull($config['id_property']);
    }
}
