<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;

class MainConfigurationTest extends  \PHPUnit_Framework_TestCase
{
    protected $storageFactories = array();
    
    protected function setUp()
    {
        $this->storageFactories = array(
            new DoctrineStorageFactory,
            new FilesystemStorageFactory
        );
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithMinimumConfig()
    {
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
    public function shouldPassConfigurationProcessingWithMinimumConfigPlusGateway()
    {
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
    public function shouldPassConfigurationProcessingWithDynamicGateways()
    {
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
    public function shouldPassConfigurationProcessingWithDynamicGatewaysAndEncryption()
    {
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
    public function shouldPassConfigurationProcessingWithDynamicGatewaysPlusSonataAdmin()
    {
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
    public function shouldPassConfigurationProcessingWithKlarnaCheckoutGatewayFactory()
    {
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
    public function shouldPassConfigurationProcessingWithDoctrineStorageFactory()
    {
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
    public function shouldPassConfigurationProcessingWithFilesystemStorageFactory()
    {
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
}