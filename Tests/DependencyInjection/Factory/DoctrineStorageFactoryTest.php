<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class DoctrineStorageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorageFactory(): void
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments(): void
    {
        $this->expectNotToPerformAssertions();
        new DoctrineStorageFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName(): void
    {
        $factory = new DoctrineStorageFactory;

        $this->assertEquals('doctrine', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration(): void
    {
        $factory = new DoctrineStorageFactory;

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'driver' => 'orm',
        )));

        $this->assertArrayHasKey('driver', $config);
        $this->assertEquals('orm', $config['driver']);
    }

    /**
     * @test
     */
    public function shouldAllowAddShortConfiguration(): void
    {
        $factory = new DoctrineStorageFactory;

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array('orm'));

        $this->assertArrayHasKey('driver', $config);
        $this->assertEquals('orm', $config['driver']);
    }

    /**
     * @test
     */
    public function shouldRequireDriverOption(): void
    {
        $this->expectExceptionMessageMatches("/The child (node|config) \"driver\" (at path|under) \"foo\" must be configured\./");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $factory = new DoctrineStorageFactory;

        $tb = new TreeBuilder('foo');
        $rootNode = $tb->getRootNode();

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }
}
