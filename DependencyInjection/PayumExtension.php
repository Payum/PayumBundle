<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Bundle\PayumBundle\ReplyToSymfonyResponseConverter;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Storage\CryptoStorageDecorator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\CustomStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel1StorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel2StorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;

class PayumExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var StorageFactoryInterface[]
     */
    protected array $storagesFactories = array();

    public function __construct()
    {
        $this->addStorageFactory(new FilesystemStorageFactory);
        $this->addStorageFactory(new DoctrineStorageFactory);
        $this->addStorageFactory(new CustomStorageFactory);
        $this->addStorageFactory(new Propel1StorageFactory);
        $this->addStorageFactory(new Propel2StorageFactory);
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $mainConfig = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($mainConfig, $configs);

        // load services
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('payum.xml');
        $loader->load('commands.xml');
        $loader->load('controller.xml');
        $loader->load('form.xml');

        if ($container->getParameter('kernel.debug')) {
            $loader->load('debug.xml');
        }

        $this->loadStorages($config['storages'], $container);
        $this->loadSecurity($config['security'], $container);

        $this->loadCoreGateway($config['gateways']['core'] ?? [], $container);
        unset($config['gateways']['core']);

        $this->loadGateways($config['gateways'], $container);

        if (isset($config['dynamic_gateways'])) {
            $this->loadDynamicGateways($config['dynamic_gateways'], $container);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle'])) {
            $config = array_merge(...$container->getExtensionConfig('doctrine'));

            // do not register mappings if dbal not configured.
            if (!empty($config['dbal']) && !empty($config['orm'])) {
                $rc = new \ReflectionClass(Gateway::class);
                $payumRootDir = dirname($rc->getFileName());

                $container->prependExtensionConfig('doctrine', array(
                    'orm' => array(
                        'mappings' => array(
                            'payum' => array(
                                'is_bundle' => false,
                                'type' => 'xml',
                                'dir' => $payumRootDir.'/Bridge/Doctrine/Resources/mapping',
                                'prefix' => 'Payum\Core\Model',
                            ),
                        ),
                    ),
                ));
            }
        }
    }

    protected function loadGateways(array $config, ContainerBuilder $container): void
    {
        $builder = $container->getDefinition('payum.builder');

        foreach ($config as $gatewayName => $gatewayConfig) {
            $builder->addMethodCall('addGateway', [$gatewayName, $gatewayConfig]);
        }
    }

    protected function loadCoreGateway(array $config, ContainerBuilder $container): void
    {
        $builder = $container->getDefinition('payum.builder');

        $defaultConfig = [
            'payum.template.layout' => '@PayumCore\layout.html.twig',
            'payum.template.obtain_credit_card' => '@PayumSymfonyBridge\obtainCreditCard.html.twig',
            'payum.paths' => [
                'PayumSymfonyBridge' => dirname((new \ReflectionClass(ReplyToSymfonyResponseConverter::class))->getFileName()).'/Resources/views',
            ],

            'payum.action.get_http_request' => new Reference('payum.action.get_http_request'),
            'payum.action.obtain_credit_card' => new Reference('payum.action.obtain_credit_card_builder'),
        ];

        $config = array_replace_recursive($defaultConfig, $config);

        $builder->addMethodCall('addCoreGatewayFactoryConfig', [$config]);
    }

    protected function loadStorages(array $config, ContainerBuilder $container): void
    {
        foreach ($config as $modelClass => $storageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($storageConfig);
            $storageId = $this->storagesFactories[$storageFactoryName]->create(
                $container,
                $modelClass,
                $storageConfig[$storageFactoryName]
            );

            $container->getDefinition($storageId)->addTag('payum.storage', array('model_class' => $modelClass));

            if (str_contains($storageId, '.storage.')) {
                $storageExtensionId = str_replace('.storage.', '.extension.storage.', $storageId);
            } else {
                throw new LogicException(sprintf('In order to add storage to extension the storage "%s" has to contains ".storage." inside.', $storageId));
            }

            $storageExtension = new ChildDefinition('payum.extension.storage.prototype');
            $storageExtension->replaceArgument(0, new Reference($storageId));
            $storageExtension->setPublic(true);
            $container->setDefinition($storageExtensionId, $storageExtension);

            if ($storageConfig['extension']['all']) {
                $storageExtension->addTag('payum.extension', array('all' => true));
            } else {
                foreach ($storageConfig['extension']['gateways'] as $gatewayName) {
                    $storageExtension->addTag('payum.extension', array('gateway' => $gatewayName));
                }

                foreach ($storageConfig['extension']['factories'] as $factory) {
                    $storageExtension->addTag('payum.extension', array('factory' => $factory));
                }
            }
        }
    }

    protected function loadSecurity(array $securityConfig, ContainerBuilder $container): void
    {
        foreach ($securityConfig['token_storage'] as $tokenClass => $tokenStorageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($tokenStorageConfig);

            $storageId = $this->storagesFactories[$storageFactoryName]->create(
                $container,
                $tokenClass,
                $tokenStorageConfig[$storageFactoryName]
            );

            $container->setDefinition('payum.security.token_storage', new ChildDefinition($storageId));
        }
    }

    protected function loadDynamicGateways(array $dynamicGatewaysConfig, ContainerBuilder $container): void
    {
        $configClass = null;
        $configStorage = null;
        foreach ($dynamicGatewaysConfig['config_storage'] as $configClass => $configStorageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($configStorageConfig);

            $configStorage = $this->storagesFactories[$storageFactoryName]->create(
                $container,
                $configClass,
                $configStorageConfig[$storageFactoryName]
            );

            $container->setDefinition('payum.dynamic_gateways.config_storage', new ChildDefinition($configStorage));
        }


        if (isset($dynamicGatewaysConfig['encryption']['defuse_secret_key'])) {
            $container->register('payum.dynamic_gateways.cypher', DefuseCypher::class)
                ->addArgument($dynamicGatewaysConfig['encryption']['defuse_secret_key'])
            ;
            $container->register('payum.dynamic_gateways.encrypted_config_storage', CryptoStorageDecorator::class)
                ->setPublic(false)
                ->setDecoratedService('payum.dynamic_gateways.config_storage')
                ->addArgument(new Reference('payum.dynamic_gateways.encrypted_config_storage.inner'))
                ->addArgument(new Reference('payum.dynamic_gateways.cypher'))
            ;


        }

        //deprecated
        $registry = new Definition(DynamicRegistry::class, array(
            new Reference('payum.dynamic_gateways.config_storage'),
            new Reference('payum.static_registry')
        ));
        $registry->setPublic(true);

        $container->setDefinition('payum.dynamic_registry', $registry);

        if ($dynamicGatewaysConfig['sonata_admin']) {
            throw new \LogicException('Not supported. Has to wait till Sonata Admin 4.x will be released.');

            if (false === class_exists(AbstractAdmin::class)) {
                throw new LogicException('Admin class does not exists. Did you install SonataAdmin bundle?');
            }

            $gatewayConfigAdmin =  new Definition(GatewayConfigAdmin::class, [
                null,
                $configClass,
                null
            ]);
            $gatewayConfigAdmin->addMethodCall('setFormFactory', [new Reference('form.factory')]);

            if ($container->hasDefinition('payum.dynamic_gateways.cypher')) {
                $gatewayConfigAdmin->addMethodCall('setCypher', [new Reference('payum.dynamic_gateways.cypher')]);
            }

            $gatewayConfigAdmin->addTag('sonata.admin', [
                'manager_type' => 'orm',
                'group' => "Gateways",
                'label' =>  "Configs",
            ]);

            $container->setDefinition('payum.dynamic_gateways.gateway_config_admin', $gatewayConfigAdmin);
        }

        $payumBuilder = $container->getDefinition('payum.builder');
        $payumBuilder->addMethodCall('setGatewayConfigStorage', [new Reference('payum.dynamic_gateways.config_storage')]);
    }

    /**
     * @throws \Payum\Core\Exception\InvalidArgumentException
     */
    public function addStorageFactory(StorageFactoryInterface $factory): void
    {
        $factoryName = $factory->getName();
        if (empty($factoryName)) {
            throw new InvalidArgumentException(sprintf('The storage factory %s has empty name', get_class($factory)));
        }
        if (array_key_exists($factoryName, $this->storagesFactories)) {
            throw new InvalidArgumentException(sprintf('The storage factory with such name %s already registered', $factoryName));
        }

        $this->storagesFactories[$factoryName] = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): MainConfiguration
    {
        return new MainConfiguration($this->storagesFactories);
    }

    protected function findSelectedStorageFactoryNameInStorageConfig(array $storageConfig): string
    {
        foreach ($storageConfig as $name => $value) {
            if (isset($this->storagesFactories[$name])) {
                return $name;
            }
        }

        throw new \RuntimeException('StorageFactoryName not found');
    }
}
