<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class MainConfigurationTest extends TestCase
{
    protected array $storageFactories = array();

    protected function setUp(): void
    {
        $this->storageFactories = array(
            new DoctrineStorageFactory,
            new FilesystemStorageFactory
        );
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithMinimumConfig(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array()
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithMinimumConfigPlusGateway(): void
    {
        $this->expectNotToPerformAssertions();
        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'factory' => 'paypal_express_checkout',
                        'username' => 'aUsername',
                        'password' => 'aPassword',
                        'signature' => 'aSignature',
                        'sandbox' => true,
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithDynamicGateways(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'dynamic_gateways' => array(
                    'config_storage' => array(
                        'Payum\Core\Model\GatewayConfig' => array(
                            'doctrine' => array(
                                'driver' => 'aDriver',
                            )
                        ),
                    ),
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithDynamicGatewaysAndEncryption(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'dynamic_gateways' => array(
                    'encryption' => [
                        'defuse_secret_key' =>  'aSecretKey',
                    ],
                    'config_storage' => array(
                        'Payum\Core\Model\GatewayConfig' => array(
                            'doctrine' => array(
                                'driver' => 'aDriver',
                            )
                        ),
                    ),
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithDynamicGatewaysPlusSonataAdmin(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'dynamic_gateways' => array(
                    'sonata_admin' => true,
                    'config_storage' => array(
                        'Payum\Core\Model\GatewayConfig' => array(
                            'doctrine' => array(
                                'driver' => 'aDriver',
                            )
                        ),
                    ),
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithKlarnaCheckoutGatewayFactory(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'klarna_checkout' => array(
                            'secret' => 'aSecret',
                            'merchant_id' => 'anId',
                            'sandbox' => true
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithDoctrineStorageFactory(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    'stdClass' => array(
                        'doctrine' => array(
                            'driver' => 'aDriver',
                        )
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'omnipay_direct' => array(
                            'type' => 'PayPal_Express',
                            'options' => array(),
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithFilesystemStorageFactory(): void
    {
        $this->expectNotToPerformAssertions();

        $configuration = new MainConfiguration($this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    'stdClass' => array(
                        'filesystem' => array(
                            'storage_dir' => 'a_dir',
                            'id_property' => 'aProp',
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'omnipay_offsite' => array(
                            'type' => 'PayPal_Express',
                            'options' => array(),
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithHaliteKeyAndPreviousDefuseKey(): void
    {
        $config = $this->processEncryption([
            'halite_key' => 'aHaliteKey',
            'previous' => ['defuse_secret_key' => 'aDefuseKey'],
        ]);

        $this->assertSame('aHaliteKey', $config['dynamic_gateways']['encryption']['halite_key']);
        $this->assertSame('aDefuseKey', $config['dynamic_gateways']['encryption']['previous']['defuse_secret_key']);
    }

    /**
     * @test
     */
    public function throwIfBothEncryptionKeysAreSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Set either "defuse_secret_key" or "halite_key", not both.');

        $this->processEncryption(['halite_key' => 'aHaliteKey', 'defuse_secret_key' => 'aDefuseKey']);
    }

    /**
     * @test
     */
    public function throwIfPreviousKeyIsSetWithoutACurrentKey(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"previous" needs a "defuse_secret_key" or "halite_key"');

        $this->processEncryption(['previous' => ['defuse_secret_key' => 'aDefuseKey']]);
    }

    /**
     * @test
     */
    public function throwIfPreviousHasNoKey(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Set "defuse_secret_key" or "halite_key" in "previous".');

        $this->processEncryption(['halite_key' => 'aHaliteKey', 'previous' => []]);
    }

    private function processEncryption(array $encryption): array
    {
        return (new Processor())->processConfiguration(new MainConfiguration($this->storageFactories), [
            'payum' => [
                'security' => [
                    'token_storage' => [
                        'Payum\Core\Model\Token' => [
                            'filesystem' => ['storage_dir' => sys_get_temp_dir(), 'id_property' => 'hash'],
                        ],
                    ],
                ],
                'dynamic_gateways' => [
                    'encryption' => $encryption,
                    'config_storage' => [
                        'Payum\Core\Model\GatewayConfig' => [
                            'doctrine' => ['driver' => 'aDriver'],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
