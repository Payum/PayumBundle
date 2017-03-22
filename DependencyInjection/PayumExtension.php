<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Storage\EncryptingGatewayConfigurationStorage;
use Sonata\AdminBundle\Admin\Admin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class PayumExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var StorageFactoryInterface[]
     */
    protected $storagesFactories = array();

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $mainConfig = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($mainConfig, $configs);

        // load services
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('payum.xml');
        $loader->load('form.xml');

        if ($container->getParameter('kernel.debug')) {
            $loader->load('debug.xml');
        }

        $this->loadStorages($config['storages'], $container);
        $this->loadSecurity($config['security'], $container);

        $this->loadCoreGateway(isset($config['gateways']['core']) ? $config['gateways']['core'] : [], $container);
        unset($config['gateways']['core']);
        
        $this->loadGateways($config['gateways'], $container);

        if (isset($config['dynamic_gateways'])) {
            $this->loadDynamicGateways($config['dynamic_gateways'], $config['storage_encrypting'], $container);
        };
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle'])) {
            foreach ($container->getExtensionConfig('doctrine') as $config) {
                // do not register mappings if dbal not configured.
                if (false == empty($config['dbal'])) {
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

                    break;
                }
            }
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadGateways(array $config, ContainerBuilder $container)
    {
        $builder = $container->getDefinition('payum.builder');

        foreach ($config as $gatewayName => $gatewayConfig) {
            $builder->addMethodCall('addGateway', [$gatewayName, $gatewayConfig]);
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadCoreGateway(array $config, ContainerBuilder $container)
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

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadStorages(array $config, ContainerBuilder $container)
    {
        foreach ($config as $modelClass => $storageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($storageConfig);
            $storageId = $this->storagesFactories[$storageFactoryName]->create(
                $container,
                $modelClass,
                $storageConfig[$storageFactoryName]
            );

            $container->getDefinition($storageId)->addTag('payum.storage', array('model_class' => $modelClass));

            if (false !== strpos($storageId, '.storage.')) {
                $storageExtensionId = str_replace('.storage.', '.extension.storage.', $storageId);
            } else {
                throw new LogicException(sprintf('In order to add storage to extension the storage "%s" has to contains ".storage." inside.', $storageId));
            }

            $storageExtension = new DefinitionDecorator('payum.extension.storage.prototype');
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

    /**
     * @param array $securityConfig
     * @param ContainerBuilder $container
     */
    protected function loadSecurity(array $securityConfig, ContainerBuilder $container)
    {
        foreach ($securityConfig['token_storage'] as $tokenClass => $tokenStorageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($tokenStorageConfig);

            $storageId = $this->storagesFactories[$storageFactoryName]->create(
                $container,
                $tokenClass,
                $tokenStorageConfig[$storageFactoryName]
            );

            $container->setDefinition('payum.security.token_storage', new DefinitionDecorator($storageId));
        }
    }

    /**
     * @param array $dynamicGatewaysConfig
     * @param array $storageEncryptingConfig
     * @param ContainerBuilder $container
     */
    protected function loadDynamicGateways(array $dynamicGatewaysConfig, array $storageEncryptingConfig, ContainerBuilder $container)
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

            $container->setDefinition('payum.dynamic_gateways.config_storage', new DefinitionDecorator($configStorage));
            $this->decorateStorageWithEncryptingIfConfigured($storageEncryptingConfig, 'payum.dynamic_gateways.config_storage', $container);

            $payumBuilder = $container->getDefinition('payum.builder');
            $payumBuilder->addMethodCall('setGatewayConfigStorage', [new Reference('payum.dynamic_gateways.config_storage')]);
        }

        //deprecated
        $registry =  new Definition('Payum\Core\Registry\DynamicRegistry', array(
            new Reference('payum.dynamic_gateways.config_storage'),
            new Reference('payum.static_registry')
        ));
        $container->setDefinition('payum.dynamic_registry', $registry);

        if ($dynamicGatewaysConfig['sonata_admin']) {
            if (false == class_exists(Admin::class)) {
                throw new LogicException('Admin class does not exists. Did you install SonataAdmin bundle?');
            }

            $gatewayConfigAdmin =  new Definition('Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin', array(
                null,
                $configClass,
                null
            ));
            $gatewayConfigAdmin->addMethodCall('setFormFactory', array(new Reference('form.factory')));
            $gatewayConfigAdmin->addTag('sonata.admin', array(
                'manager_type' => 'orm',
                'group' => "Gateways",
                'label' =>  "Configs",
            ));

            $container->setDefinition('payum.dynamic_gateways.gateway_config_admin', $gatewayConfigAdmin);
        }
    }

    /**
     * @param Factory\Storage\StorageFactoryInterface $factory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException
     */
    public function addStorageFactory(StorageFactoryInterface $factory)
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
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new MainConfiguration($this->storagesFactories);
    }

    /**
     * @param array $storageConfig
     *
     * @return string
     */
    protected function findSelectedStorageFactoryNameInStorageConfig($storageConfig)
    {
        foreach ($storageConfig as $name => $value) {
            if (isset($this->storagesFactories[$name])) {
                return $name;
            }
        }
    }

    /**
     * @param array $config
     * @param string $id
     * @param ContainerBuilder $container
     */
    protected function decorateStorageWithEncryptingIfConfigured(array $config, $id, ContainerBuilder $container)
    {
        if ($config['enabled']) {
            $container->setParameter('payum.storage.encrypting_algorithm', $config['algorithm']);
            $container->setParameter('payum.storage.encrypting_initialization_vector', $config['initialization_vector']);

            $decoratingId = $id . '_encrypted';
            $container->register($decoratingId, EncryptingGatewayConfigurationStorage::class)
                ->setDecoratedService($id)
                ->setArguments([
                    new Reference($decoratingId . '.inner'),
                    $config['algorithm'],
                    $config['secret'],
                    $config['initialization_vector']
                ])
                ->setPublic(false)
            ;
        }
    }
}
