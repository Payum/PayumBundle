<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use ParagonIE\Halite\KeyFactory;
use Payum\Bundle\PayumBundle\Command\ReencryptGatewayConfigsCommand;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Bridge\Halite\Security\HaliteCypher;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Storage\CryptoStorageDecorator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PayumExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldAddGatewayTagWithCorrectGatewayAndFactoryNamesSet(): void
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
    public function shouldUsePayumBuilderServiceToBuildPayumService(): void
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

        $extension->load($configs, $containerBuilder);

        $payum = $containerBuilder->getDefinition('payum');
        $this->assertEquals('Payum\Core\Payum', $payum->getClass());
        $this->assertIsArray($payum->getFactory());

        $this->assertInstanceOf(Reference::class, $payum->getFactory()[0]);
        $this->assertEquals('payum.builder', (string) $payum->getFactory()[0]);

        $this->assertEquals('getPayum', $payum->getFactory()[1]);
    }

    /**
     * @test
     */
    public function shouldSetGatewayConfigStorageToPayumBuilderIfConfigured(): void
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

        $extension->load($configs, $containerBuilder);

        $builder = $containerBuilder->getDefinition('payum.builder');

        $calls = $builder->getMethodCalls();

        $this->assertNotEmpty(array_filter($calls, static fn (array $call): bool => 'addCoreGatewayFactoryConfig' === $call[0]));

        $setConfigStorage = array_values(array_filter($calls, static fn (array $call): bool => 'setGatewayConfigStorage' === $call[0]));

        $this->assertCount(1, $setConfigStorage);
        $this->assertEquals('payum.dynamic_gateways.config_storage', (string) $setConfigStorage[0][1][0]);
    }

    /**
     * @test
     */
    public function shouldWrapGatewayConfigStorageByEncryptionDecoratorWhenDefuseEncryptionIsEnabled(): void
    {
        $config = array(
            'dynamic_gateways' => array(
                'encryption' => [
                    'defuse_secret_key' =>  'aSecretKey',
                ],
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

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        $extension = new PayumExtension;

        $extension->load($configs, $container);

        $this->assertTrue($container->hasDefinition('payum.dynamic_gateways.cypher'));
        $cypher = $container->getDefinition('payum.dynamic_gateways.cypher');
        $this->assertSame(DefuseCypher::class, $cypher->getClass());
        $this->assertSame('aSecretKey', $cypher->getArgument(0));

        $this->assertTrue($container->hasDefinition('payum.dynamic_gateways.encrypted_config_storage'));

        $storage = $container->getDefinition('payum.dynamic_gateways.encrypted_config_storage');
        $this->assertSame(CryptoStorageDecorator::class, $storage->getClass());
        $this->assertSame('payum.dynamic_gateways.encrypted_config_storage.inner', (string) $storage->getArgument(0));
        $this->assertSame('payum.dynamic_gateways.cypher', (string) $storage->getArgument(1));
        $this->assertSame('payum.dynamic_gateways.config_storage', $storage->getDecoratedService()[0]);
    }

    /**
     * @test
     */
    public function shouldConfigureSonataAdminClassForGatewayConfigModelSetInStorageSection(): void
    {
        $this->markTestSkipped('Has to wait for Sonata Admin Sf4 compatible release');

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

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.dynamic_gateways.gateway_config_admin'));
        $configAdmin = $containerBuilder->getDefinition('payum.dynamic_gateways.gateway_config_admin');

        $this->assertEquals('Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin', $configAdmin->getClass());
        $this->assertEquals('Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection\TestGatewayConfig', $configAdmin->getArgument(1));

        $this->assertEquals(
            array(array('manager_type' => 'orm', 'group' => 'Gateways', 'label' => 'Configs')),
            $configAdmin->getTag('sonata.admin')
        );

        $this->assertCount(1, $configAdmin->getMethodCalls());
    }

    /**
     * @test
     */
    public function shouldInjectCypherToForGatewayConfigAdmin(): void
    {
        $this->markTestSkipped('Has to wait for Sonata Admin Sf4 compatible release');

        $config = array(
            'dynamic_gateways' => array(
                'sonata_admin' => true,
                'encryption' => [
                    'defuse_secret_key' =>  'aSecretKey',
                ],
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

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.dynamic_gateways.gateway_config_admin'));
        $configAdmin = $containerBuilder->getDefinition('payum.dynamic_gateways.gateway_config_admin');

        $calls = $configAdmin->getMethodCalls();

        $this->assertCount(2, $calls);

        $this->assertSame('setCypher', $calls[1][0]);
        $this->assertSame('payum.dynamic_gateways.cypher', (string) $calls[1][1][0]);
    }

    /**
     * @test
     */
    public function shouldNotConfigureSonataAdminClassForGatewayConfigIfDisabled(): void
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

        $extension->load($configs, $containerBuilder);

        $this->assertFalse($containerBuilder->hasDefinition('payum.dynamic_gateways.gateway_config_admin'));
    }

    /**
     * @test
     */
    public function shouldUseHaliteCypherWhenHaliteKeyIsSet(): void
    {
        if (! class_exists(HaliteCypher::class) || ! class_exists(KeyFactory::class)) {
            $this->markTestSkipped('Needs payum/core 2.0 and paragonie/halite.');
        }

        $container = $this->loadWithEncryption(['halite_key' => 'aHaliteKey']);

        $cypher = $container->getDefinition('payum.dynamic_gateways.cypher');
        $this->assertSame(HaliteCypher::class, $cypher->getClass());
        $this->assertSame('aHaliteKey', $cypher->getArgument(0));
        $this->assertSame('payum.dynamic_gateways.cypher', (string) $container->getDefinition('payum.dynamic_gateways.encrypted_config_storage')->getArgument(1));
    }

    /**
     * @test
     */
    public function shouldRefuseHaliteKeyWithoutHaliteCypher(): void
    {
        if (class_exists(HaliteCypher::class) && class_exists(KeyFactory::class)) {
            $this->markTestSkipped('Halite is installed.');
        }

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('"halite_key"');

        $this->loadWithEncryption(['halite_key' => 'aHaliteKey']);
    }

    /**
     * @test
     */
    public function shouldRegisterReencryptCommandWithThePreviousCypher(): void
    {
        $container = $this->loadWithEncryption([
            'defuse_secret_key' => 'aNewKey',
            'previous' => ['defuse_secret_key' => 'anOldKey'],
        ]);

        $previous = $container->getDefinition('payum.dynamic_gateways.previous_cypher');
        $this->assertSame(DefuseCypher::class, $previous->getClass());
        $this->assertSame('anOldKey', $previous->getArgument(0));

        $command = $container->getDefinition('payum.command.reencrypt_gateway_configs');
        $this->assertSame(ReencryptGatewayConfigsCommand::class, $command->getClass());
        $this->assertTrue($command->hasTag('console.command'));
        $this->assertSame('payum.dynamic_gateways.encrypted_config_storage.inner', (string) $command->getArgument(0));
        $this->assertSame('payum.dynamic_gateways.cypher', (string) $command->getArgument(1));
        $this->assertSame('payum.dynamic_gateways.previous_cypher', (string) $command->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldRegisterReencryptCommandWithoutAPreviousCypher(): void
    {
        $container = $this->loadWithEncryption(['defuse_secret_key' => 'aKey']);

        $this->assertFalse($container->hasDefinition('payum.dynamic_gateways.previous_cypher'));
        $this->assertNull($container->getDefinition('payum.command.reencrypt_gateway_configs')->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldNotRegisterReencryptCommandWithoutEncryption(): void
    {
        $container = $this->loadWithEncryption([]);

        $this->assertFalse($container->hasDefinition('payum.dynamic_gateways.cypher'));
        $this->assertFalse($container->hasDefinition('payum.command.reencrypt_gateway_configs'));
    }

    private function loadWithEncryption(array $encryption): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        (new PayumExtension())->load([[
            'dynamic_gateways' => [
                'encryption' => $encryption,
                'config_storage' => [
                    'Payum\Core\Model\GatewayConfig' => [
                        'filesystem' => ['storage_dir' => sys_get_temp_dir(), 'id_property' => 'hash'],
                    ],
                ],
            ],
            'security' => [
                'token_storage' => [
                    'Payum\Core\Model\Token' => [
                        'filesystem' => ['storage_dir' => sys_get_temp_dir(), 'id_property' => 'hash'],
                    ],
                ],
            ],
            'gateways' => [],
        ]], $container);

        return $container;
    }
}

class TestGatewayConfig implements GatewayConfigInterface
{
    public function getGatewayName()
    {
    }

    public function setGatewayName($gatewayName): void
    {
    }

    public function getFactoryName()
    {
    }

    public function setFactoryName($name): void
    {
    }

    public function setConfig(array $config): void
    {
    }

    public function getConfig()
    {
    }
}

