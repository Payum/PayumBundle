<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Payum\Core\Model\GatewayConfigInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAddGatewayTagWithCorrectGatewayAndFactoryNamesSet()
    {
        $this->markTestSkipped();

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
                    'factory' => 'paypal_express_checkout_nvp',
                    'username' => 'a_username',
                    'password' => 'a_password',
                    'signature' => 'a_signature',
                    'sandbox' => true,
                ),
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $gatewayDefinition = $containerBuilder->getDefinition('payum.paypal_express_checkout.the_paypal_gateway.gateway');

        $tagAttributes = $gatewayDefinition->getTag('payum.gateway');

        $this->assertCount(1, $tagAttributes);

        $attributes = $tagAttributes[0];

        $this->assertArrayHasKey('factory', $attributes);
        $this->assertEquals('paypal_express_checkout', $attributes['factory']);

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

        $this->assertEquals('addCoreGatewayFactoryConfig', $calls[7][0]);

        $this->assertEquals('setGatewayConfigStorage', $calls[8][0]);
        $this->assertEquals('payum.dynamic_gateways.config_storage', (string) $calls[8][1][0]);
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