<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;

class PayumExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfExtension()
    {
        $rc = new \ReflectionClass(PayumExtension::class);

        $this->assertTrue($rc->isSubclassOf(Extension::class));
    }

    /**
     * @test
     */
    public function shouldImplementPrependExtensionInterface()
    {
        $rc = new \ReflectionClass(PayumExtension::class);

        $this->assertTrue($rc->implementsInterface(PrependExtensionInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayumExtension;
    }

    /**
     * @test
     */
    public function shouldAllowAddStorageFactory()
    {
        $factory = $this->createMock(StorageFactoryInterface::class);
        $factory
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factory);

        $this->assertAttributeContains($factory, 'storagesFactories', $extension);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The storage factory Mock_StorageFactoryInterface_
     */
    public function throwIfTryToAddStorageFactoryWithEmptyName()
    {
        $factoryWithEmptyName = $this->createMock(StorageFactoryInterface::class);
        $factoryWithEmptyName
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(''))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factoryWithEmptyName);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The storage factory with such name theFoo already registered
     */
    public function throwIfTryToAddStorageGatewayFactoryWithNameAlreadyAdded()
    {
        $factory = $this->createMock(StorageFactoryInterface::class);
        $factory
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factory);
        $extension->addStorageFactory($factory);
    }

    /**
     * @test
     */
    public function shouldNotAddPayumMappingIfDoctrineBundleNotRegistered()
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array());

        $extension = new PayumExtension;

        $this->setupDefaultConfig($extension, $container);
        $extension->prepend($container);

        $this->assertEmpty($container->getExtensionConfig('doctrine'));
    }

    /**
     * @test
     */
    public function shouldNotAddPayumMappingIfDoctrineBundleRegisteredButDbalNotConfigured()
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $container->prependExtensionConfig('doctrine', array(
            'orm' => 'not empty',
        ));

        $this->setupDefaultConfig($extension, $container);
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
    public function shouldNotAddPayumMappingIfDoctrineBundleRegisteredButOrmNotConfigured()
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $this->setupDefaultConfig($extension, $container);

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
    public function shouldAddPayumMappingIfDoctrineBundleRegisteredWithDbalAndOrmConfiguredInSingleConfiguration()
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $container->prependExtensionConfig('doctrine', array(
            'dbal' => 'not empty',
            'orm' => 'not empty'
        ));

        $this->setupDefaultConfig($extension, $container);
        $extension->prepend($container);

        $rc = new \ReflectionClass('Payum\Core\Gateway');
        $payumRootDir = dirname($rc->getFileName());

        $this->assertEquals(
            array(
                array(
                    'orm' => array(
                        'entity_managers' => array(
                            'default' => array(
                                'mappings' => array(
                                    'payum' => array(
                                        'is_bundle' => false,
                                        'type' => 'xml',
                                        'dir' => $payumRootDir.'/Bridge/Doctrine/Resources/mapping',
                                        'prefix' => 'Payum\Core\Model',
                                    )
                                )
                            ),
                        )
                    )
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
    public function shouldAddPayumMappingIfDoctrineBundleRegisteredWithDbalAndOrmConfiguredInMultipleConfigurations()
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));
        $container->setParameter('kernel.debug', true);
        $container->prependExtensionConfig('doctrine', array(
            'dbal' => 'not empty',
            'orm' => 'not empty'
        ));

        $this->setupDefaultConfig($extension, $container);
        $extension->prepend($container);

        $rc = new \ReflectionClass('Payum\Core\Gateway');
        $payumRootDir = dirname($rc->getFileName());

        $this->assertEquals(
            array(
                array(
                    'orm' => array(
                        'entity_managers' => array(
                            'default' => array(
                                'mappings' => array(
                                    'payum' => array(
                                        'is_bundle' => false,
                                        'type' => 'xml',
                                        'dir' => $payumRootDir.'/Bridge/Doctrine/Resources/mapping',
                                        'prefix' => 'Payum\Core\Model',
                                    )
                                )
                            ),
                        )
                    )
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
    public function shouldAddPayumMappingIfDoctrineBundleRegisteredWithDbalAndOrmConfiguredWithCustomEntityManager()
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));
        $container->setParameter('kernel.debug', true);
        $container->prependExtensionConfig('doctrine', array(
            'dbal' => 'not empty',
            'orm' => 'not empty'
        ));
        $this->setupDefaultConfig($extension, $container);

        $container->prependExtensionConfig($extension->getAlias(),
            [
                'entity_manager' => 'custom_em'
            ]
        );

        $extension->prepend($container);

        $rc = new \ReflectionClass('Payum\Core\Gateway');
        $payumRootDir = dirname($rc->getFileName());

        $this->assertEquals(
            array(
                array(
                    'orm' => array(
                        'entity_managers' => array(
                            'custom_em' => array(
                                'mappings' => array(
                                    'payum' => array(
                                        'is_bundle' => false,
                                        'type' => 'xml',
                                        'dir' => $payumRootDir.'/Bridge/Doctrine/Resources/mapping',
                                        'prefix' => 'Payum\Core\Model',
                                    )
                                )
                            ),
                        )
                    )
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
    public function shouldAddGatewaysToBuilder()
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

    private function setupDefaultConfig(PayumExtension $extension, ContainerBuilder $container)
    {
        $extension->addStorageFactory(new FeeStorageFactory());

        $container->prependExtensionConfig($extension->getAlias(),
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
        );
    }
}

class FeeStorageFactory implements StorageFactoryInterface
{
    public function create(ContainerBuilder $container, $modelClass, array $config)
    {
        return 'aStorageId';
    }

    public function getName()
    {
        return 'bar_storage';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
            ->scalarNode('bar_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}
