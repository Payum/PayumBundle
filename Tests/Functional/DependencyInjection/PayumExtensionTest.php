<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Payum\Core\Model\GatewayConfigInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
    public function provideGateways()
    {
        return array(
            array(
                'factory' => 'paypal_express_checkout',
                'username' => 'a_username',
                'password' => 'a_password',
                'signature' => 'a_signature',
                'sandbox' => true
            ),
            array(
                'factory' => 'paypal_pro_checkout',
                'username' => 'a_username',
                'password' => 'a_password',
                'partner' => 'a_partner',
                'vendor' => 'a_vendor',
                'sandbox' => true
            ),
            array(
                'factory' => 'be2bill_direct',
                'identifier' => 'a_identifier',
                'password' => 'a_password',
                'sandbox' => true
            ),
            array(
                'factory' => 'be2bill_offsite',
                'identifier' => 'a_identifier',
                'password' => 'a_password',
                'sandbox' => true
            ),
            array(
                'factory' => 'be2bill',
                'identifier' => 'a_identifier',
                'password' => 'a_password',
                'sandbox' => true
            ),
            array(
                'factory' => 'offline',
            ),
            array(
                'factory' => 'stripe_js',
                'publishable_key' => 'a_key',
                'secret_key' => 'a_key'
            ),
            array(
                'factory' => 'stripe_checkout',
                'publishable_key' => 'a_key',
                'secret_key' => 'a_key'
            ),
            array(
                'factory' => 'authorize_net_aim',
                'login_id' => 'a_login',
                'transaction_key' => 'a_transaction_key',
                'sandbox' => true
            ),
            array(
                'factory' => 'omnipay_direct',
                'type' => 'Stripe',
                'apiKey' => 'abc123',
            ),
            array(
                'factory' => 'omnipay_offsite',
                'type' => 'PayPal_Express',
                'username' => 'abc123',
                'passowrd' => 'abc123',
                'signature' => 'abc123',
                'testMode' => true,
            ),
            array(
                'factory' => 'payex',
                'encryption_key' => 'aKey',
                'account_number' => 'aNum'
            ),
            array(
                'factory' => 'klarna_checkout',
                'secret' => 'aSecret',
                'merchant_id' => 'anId'
            ),
            array(
                'factory' => 'klarna_invoice',
                'eid' => 'anId',
                'secret' => 'aSecret',
            ),
        );
    }

    /**
     * @test
     *
     * @dataProvider provideGateways
     */
    public function shouldLoadExtensionWithGateway($config)
    {
        $config = array(
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
                'a_gateway' => $config
            )
        );

        $configs = array($config);

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $container);

        $this->assertTrue($container->hasDefinition('payum.'.$gatewayFactory->getName().'.factory'));
        $this->assertTrue($container->hasDefinition('payum.'.$gatewayFactory->getName().'.a_gateway.gateway'));

        $factory = $container->getDefinition('payum.'.$gatewayFactory->getName().'.a_gateway.gateway')->getFactory();
        $this->assertNotEmpty($factory);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factory[0]);
        $this->assertEquals('payum.'.$gatewayFactory->getName().'.factory', (string) $factory[0]);
        $this->assertEquals('create', $factory[1]);
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithCustomGateway()
    {
        $config = array(
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
                    'custom' => array(
                        'service' => 'aServiceId',
                    ),
                )
            )
        );

        $configs = array($config);

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $container);

        $this->assertTrue($container->hasDefinition('payum.custom.a_gateway.gateway'));
    }

    /**
     * @test
     */
    public function shouldAddGatewayTagWithCorrectGatewayAndFactoryNamesSet()
    {
        $config = array(
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
                'the_paypal_gateway' => array(
                    'paypal_express_checkout_nvp' => array(
                        'username' => 'a_username',
                        'password' => 'a_password',
                        'signature' => 'a_signature',
                        'sandbox' => true
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $gatewayDefinition = $containerBuilder->getDefinition('payum.paypal_express_checkout_nvp.the_paypal_gateway.gateway');

        $tagAttributes = $gatewayDefinition->getTag('payum.gateway');

        $this->assertCount(1, $tagAttributes);

        $attributes = $tagAttributes[0];

        $this->assertArrayHasKey('factory', $attributes);
        $this->assertEquals('paypal_express_checkout_nvp', $attributes['factory']);

        $this->assertArrayHasKey('gateway', $attributes);
        $this->assertEquals('the_paypal_gateway', $attributes['gateway']);
    }

    /**
     * @test
     */
    public function shouldUsePayumBuilderServiceToBuildPayumService()
    {
        $config = array(
            // 'dynamic_gateways' => array()
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
            'gateways' => array(),
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $payum = $containerBuilder->getDefinition('payum');
        $this->assertEquals('Payum\Core\Payum', $payum->getClass());
        $this->assertInternalType('array', $payum->getFactory());

        $this->assertInstanceOf(Reference::class, $payum->getFactory()[0]);
        $this->assertEquals('payum.builder', (string) $payum->getFactory()[0]);

        $this->assertEquals('getPayum', $payum->getFactory()[1]);
    }

    /**
     * @test
     */
    public function shouldSetGatewayConfigStorageToPayumBuilderIfConfigured()
    {
        $config = array(
            'dynamic_gateways' => array(
                'config_storage' => array(
                    'Payum\Core\Model\GatewayConfig' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
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
            'gateways' => array(),
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $builder = $containerBuilder->getDefinition('payum.builder');

        $calls = $builder->getMethodCalls();

        $this->assertEquals('setGatewayConfigStorage', $calls[7][0]);
        $this->assertEquals('payum.dynamic_gateways.config_storage', (string) $calls[7][1][0]);
    }

    /**
     * @test
     */
    public function shouldConfigureSonataAdminClassForGatewayConfigModelSetInStorageSection()
    {
        $config = array(
            'dynamic_gateways' => array(
                'sonata_admin' => true,
                'config_storage' => array(
                    'Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection\TestGatewayConfig' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
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
            'gateways' => array(),
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.dynamic_gateways.gateway_config_admin'));
        $configAdmin = $containerBuilder->getDefinition('payum.dynamic_gateways.gateway_config_admin');

        $this->assertEquals('Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin', $configAdmin->getClass());
        $this->assertEquals('Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection\TestGatewayConfig', $configAdmin->getArgument(1));

        $this->assertEquals(
            array(array('manager_type' => 'orm', 'group' => 'Gateways', 'label' => 'Configs')),
            $configAdmin->getTag('sonata.admin')
        );
    }

    /**
     * @test
     */
    public function shouldNotConfigureSonataAdminClassForGatewayConfigIfDisabled()
    {
        $config = array(
            'dynamic_gateways' => array(
                'sonata_admin' => false,
                'config_storage' => array(
                    'Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection\TestGatewayConfig' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
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
            'gateways' => array(),
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertFalse($containerBuilder->hasDefinition('payum.dynamic_gateways.gateway_config_admin'));
    }
}

class TestGatewayConfig implements GatewayConfigInterface
{
    public function getGatewayName()
    {
    }

    public function setGatewayName($gatewayName)
    {
    }

    public function getFactoryName()
    {
    }

    public function setFactoryName($name)
    {
    }

    public function setConfig(array $config)
    {
    }

    public function getConfig()
    {
    }
}