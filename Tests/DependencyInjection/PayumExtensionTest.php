<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use PHPUnit\Framework\TestCase;

class PayumExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfExtension(): void
    {
        $rc = new \ReflectionClass(PayumExtension::class);

        $this->assertTrue($rc->isSubclassOf(Extension::class));
    }

    /**
     * @test
     */
    public function shouldImplementPrependExtensionInterface(): void
    {
        $rc = new \ReflectionClass(PayumExtension::class);

        $this->assertTrue($rc->implementsInterface(PrependExtensionInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments(): void
    {
        $this->expectNotToPerformAssertions();
        new PayumExtension;
    }

    /**
     * @test
     */
    public function shouldAllowAddStorageFactory(): void
    {
        $factory = $this->createMock(StorageFactoryInterface::class);
        $factory
            ->method('getName')
            ->willReturn('theFoo')
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factory);

        $reflectedConstraint = (new \ReflectionObject($extension))->getProperty('storagesFactories');
        $reflectedConstraint->setAccessible(true);
        $constraint = $reflectedConstraint->getValue($extension);

        $this->assertEquals($factory, $constraint["theFoo"]);
    }

    /**
     * @test
     */
    public function throwIfTryToAddStorageFactoryWithEmptyName(): void
    {
        $this->expectExceptionMessage("The storage factory Mock_StorageFactoryInterface_");
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $factoryWithEmptyName = $this->createMock(StorageFactoryInterface::class);
        $factoryWithEmptyName
            ->expects($this->once())
            ->method('getName')
            ->willReturn('')
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factoryWithEmptyName);
    }

    /**
     * @test
     */
    public function throwIfTryToAddStorageGatewayFactoryWithNameAlreadyAdded(): void
    {
        $this->expectExceptionMessage("The storage factory with such name theFoo already registered");
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $factory = $this->createMock(StorageFactoryInterface::class);
        $factory
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('theFoo')
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factory);
        $extension->addStorageFactory($factory);
    }

    /**
     * @test
     */
    public function shouldNotAddPayumMappingIfDoctrineBundleNotRegistered(): void
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array());

        $extension = new PayumExtension;

        $extension->prepend($container);

        $this->assertEmpty($container->getExtensionConfig('doctrine'));
    }

    /**
     * @test
     */
    public function shouldNotAddPayumMappingIfDoctrineBundleRegisteredButDbalNotConfigured(): void
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $container->prependExtensionConfig('doctrine', array(
            'orm' => 'not empty',
        ));

        $extension->prepend($container);

        $this->assertEquals(array(
            array(
                'orm' => 'not empty',
            )
        ), $container->getExtensionConfig('doctrine'));
    }

    /**
     * @test
     */
    public function shouldNotAddPayumMappingIfDoctrineBundleRegisteredButOrmNotConfigured(): void
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $container->prependExtensionConfig('doctrine', array(
            'dbal' => 'not empty',
        ));

        $extension->prepend($container);

        $this->assertEquals(array(
            array(
                'dbal' => 'not empty',
            )
        ), $container->getExtensionConfig('doctrine'));
    }

    /**
     * @test
     */
    public function shouldAddPayumMappingIfDoctrineBundleRegisteredWithDbalAndOrmConfiguredInSingleConfiguration(): void
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $container->prependExtensionConfig('doctrine', array(
            'dbal' => 'not empty',
            'orm' => 'not empty'
        ));

        $extension->prepend($container);

        $rc = new \ReflectionClass('Payum\Core\Gateway');
        $payumRootDir = dirname($rc->getFileName());

        $this->assertEquals(
            array(
                array(
                    'orm' => array('mappings' => array(
                        'payum' => array(
                            'is_bundle' => false,
                            'type' => 'xml',
                            'dir' => $payumRootDir.'/Bridge/Doctrine/Resources/mapping',
                            'prefix' => 'Payum\Core\Model',
                        )
                    )),
                ),
                array(
                    'dbal' => 'not empty',
                    'orm' => 'not empty'
                ),
            ),
            $container->getExtensionConfig('doctrine')
        );
    }

    /**
     * @test
     */
    public function shouldAddPayumMappingIfDoctrineBundleRegisteredWithDbalAndOrmConfiguredInMultipleConfigurations(): void
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $container->prependExtensionConfig('doctrine', array(
            'dbal' => 'not empty',
        ));
        $container->prependExtensionConfig('doctrine', array(
            'orm' => 'not empty',
        ));

        $extension->prepend($container);

        $rc = new \ReflectionClass('Payum\Core\Gateway');
        $payumRootDir = dirname($rc->getFileName());

        $this->assertEquals(
            array(
                array(
                    'orm' => array('mappings' => array(
                        'payum' => array(
                            'is_bundle' => false,
                            'type' => 'xml',
                            'dir' => $payumRootDir.'/Bridge/Doctrine/Resources/mapping',
                            'prefix' => 'Payum\Core\Model',
                        )
                    )),
                ),
                array(
                    'orm' => 'not empty'
                ),
                array(
                    'dbal' => 'not empty',
                ),
            ),
            $container->getExtensionConfig('doctrine')
        );
    }

    /**
     * @test
     */
    public function shouldAddGatewaysToBuilder(): void
    {
        $extension = new PayumExtension;
        $extension->addStorageFactory(new FeeStorageFactory());

        $container = new ContainerBuilder;
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.bundles', []);

        $extension->load([
            [
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'bar_storage' => ['bar_opt' => 'val']
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'foo' => 'fooVal',
                    ),
                    'another_gateway' => array(
                        'bar' => 'barVal',
                    )
                )
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('payum.builder'));

        $builder = $container->getDefinition('payum.builder');
        $calls = $builder->getMethodCalls();
        $this->assertEquals('addCoreGatewayFactoryConfig', $calls[6][0]);

        $builder = $container->getDefinition('payum.builder');
        $calls = $builder->getMethodCalls();
        $this->assertEquals('addGateway', $calls[7][0]);
        $this->assertEquals('a_gateway', $calls[7][1][0]);
        $this->assertEquals(['foo' => 'fooVal'], $calls[7][1][1]);

        $this->assertEquals('addGateway', $calls[8][0]);
        $this->assertEquals('another_gateway', $calls[8][1][0]);
        $this->assertEquals(['bar' => 'barVal'], $calls[8][1][1]);
    }
}

class FeeStorageFactory implements StorageFactoryInterface
{
    public function create(ContainerBuilder $container, $modelClass, array $config): string
    {
        return 'aStorageId';
    }

    public function getName(): string
    {
        return 'bar_storage';
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
            ->scalarNode('bar_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}
