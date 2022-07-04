<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class MainConfigurationTest extends TestCase
{
    protected array $storageFactories = [];

    protected function setUp(): void
    {
        $this->storageFactories = array(
            new FooStorageFactory(),
            new BarStorageFactory()
        );
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayOfGatewayFactoriesAndStorageFactories(): void
    {
        $this->expectNotToPerformAssertions();
        new MainConfiguration($this->storageFactories);
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessing(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->createMock('stdClass'));
        $barModelClass = get_class($this->createMock('stdClass'));

        $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    $fooModelClass => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                    $barModelClass => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    )
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => [
                        'foo' => 'fooVal'
                    ],
                    'another_gateway' => [
                        'factory' => 'aCustomFactory',
                        'bar' => 'barVal'
                    ],
                    'null_gateway' => null,
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldAddStoragesToAllGatewayByDefault(): void
    {
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->createMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    $fooModelClass => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['all']));
        $this->assertTrue($config['storages'][$fooModelClass]['extension']['all']);

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['factories']));
        $this->assertEquals(array(), $config['storages'][$fooModelClass]['extension']['factories']);

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['gateways']));
        $this->assertEquals(array(), $config['storages'][$fooModelClass]['extension']['gateways']);
    }

    /**
     * @test
     */
    public function shouldAllowDisableAddStoragesToAllGatewayFeature(): void
    {
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->createMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    $fooModelClass => array(
                        'extension' => array(
                            'all' => false,
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['all']));
        $this->assertFalse($config['storages'][$fooModelClass]['extension']['all']);
    }

    /**
     * @test
     */
    public function shouldAllowSetConcreteGatewaysWhereToAddStorages(): void
    {
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->createMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    $fooModelClass => array(
                        'extension' => array(
                            'gateways' => array(
                                'foo', 'bar'
                            )
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['gateways']));
        $this->assertEquals(array('foo', 'bar'), $config['storages'][$fooModelClass]['extension']['gateways']);
    }

    /**
     * @test
     */
    public function shouldAllowSetGatewaysCreatedWithFactoriesWhereToAddStorages(): void
    {
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->createMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    $fooModelClass => array(
                        'extension' => array(
                            'factories' => array(
                                'foo', 'bar'
                            )
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['factories']));
        $this->assertEquals(array('foo', 'bar'), $config['storages'][$fooModelClass]['extension']['factories']);
    }

    /**
     * @test
     */
    public function throwIfTryToUseNotValidClassAsStorageEntry(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.storages\": The storage entry must be a valid model class. It is set notExistClass");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    'notExistClass' => array(
                        'foo_storage' => array(
                            'foo_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfTryToAddMoreThenOneStorageForOneEntry(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.storages.stdClass\": Only one storage per entry could be selected");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    'stdClass' => array(
                        'foo_storage' => array(
                            'foo_opt' => 'bar'
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        )
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfStorageEntryDefinedWithoutConcreteStorage(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.storages.stdClass\": At least one storage must be configured.");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    'stdClass' => array(),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassIfNoneStorageSelected(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => ['foo' => 'fooVal']
                )
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfMoreThenOneTokenStorageConfigured(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.security.token_storage\": Only one token storage could be configured.");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        ),
                        'stdClass' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfTokenStorageConfiguredWithModelNotImplementingTokenInterface(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.security.token_storage\": The token class must implement `Payum\Core\Security\TokenInterface` interface");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'stdClass' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfTokenStorageConfiguredWithNotModelClass(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.security.token_storage\": The storage entry must be a valid model class.");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'foo' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfSecurityNotConfigured(): void
    {
        $this->expectExceptionMessageMatches("/The child (node|config) \"security\" (at path|under) \"payum\" must be configured\./");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            []
        ));
    }

    /**
     * @test
     */
    public function throwIfTokenStorageNotConfigured(): void
    {
        $this->expectExceptionMessageMatches("/The child (node|config) \"token_storage\" (at path|under) \"payum.security\" must be configured\./");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => [],
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfMoreThenOneGatewayConfigStorageConfigured(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.dynamic_gateways.config_storage\": Only one config storage could be configured.");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'dynamic_gateways' => array(
                    'config_storage' => array(
                        'Payum\Core\Model\GatewayConfig' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        ),
                        'stdClass' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfGatewayConfigStorageConfiguredWithModelNotImplementingGatewayConfigInterface(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.dynamic_gateways.config_storage\": The config class must implement `Payum\Core\Model\GatewayConfigInterface` interface");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'dynamic_gateways' => array(
                    'config_storage' => array(
                        'stdClass' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfGatewayConfigStorageConfiguredWithNotModelClass(): void
    {
        $this->expectExceptionMessage("Invalid configuration for path \"payum.dynamic_gateways.config_storage\": The storage entry must be a valid model class.");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'dynamic_gateways' => array(
                    'config_storage' => array(
                        'foo' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function throwIfGatewayConfigStorageNotConfigured(): void
    {
        $this->expectExceptionMessageMatches("/The child (node|config) \"config_storage\" (at path|under) \"payum.dynamic_gateways\" must be configured\./");
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'dynamic_gateways' => array(
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));
    }

    /**
     * @test
     */
    public function shouldTreatNullGatewaysV2AsEmptyArray(): void
    {
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => null,
            ),
        ));

        $this->assertEquals([], $config['gateways']);
    }

    /**
     * @test
     */
    public function shouldAllowPutAnythingToGatewaysV2AndNotPerformAnyValidations(): void
    {
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'factory' => 'aFactory',
                        'foo' => 'fooVal',
                        'bar' => 'barVal',
                    ),
                    'another_gateway' => array(
                        'factory' => 'anotherFactory',
                        'foo' => ['fooVal', 'barVal'],
                    ),
                    'gateway_with_injection' => array(
                        'foo' => '%foo%',
                        'bar' => '%bar%',
                    ),
                )
            ),
        ));

        $this->assertEquals(array(
            'a_gateway' => array(
                'factory' => 'aFactory',
                'foo' => 'fooVal',
                'bar' => 'barVal',
            ),
            'another_gateway' => array(
                'factory' => 'anotherFactory',
                'foo' => ['fooVal', 'barVal'],
            ),
            'gateway_with_injection' => array(
                'foo' => '%foo%',
                'bar' => '%bar%',
            ),
        ), $config['gateways']);
    }
}

class FooStorageFactory implements StorageFactoryInterface
{
    public function create(ContainerBuilder $container, $modelClass, array $config): string
    {
        return 'aStorageId';
    }

    public function getName(): string
    {
        return 'foo_storage';
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
            ->scalarNode('foo_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}

class BarStorageFactory implements StorageFactoryInterface
{
    public function create(ContainerBuilder $container, $modelClass, array $config): string
    {
        return 'serviceId';
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
