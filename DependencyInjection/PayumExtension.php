<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Payum\Bundle\PayumBundle\Attribute\AsPayumMiddleware;
use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Bridge\Halite\Security\HaliteCypher;
use ParagonIE\Halite\KeyFactory;
use Payum\Bundle\PayumBundle\Command\ReencryptGatewayConfigsCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Bundle\PayumBundle\ReplyToSymfonyResponseConverter;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Bridge\Doctrine\Idempotency\DbalIdempotencyStore;
use Payum\Core\Idempotency\CacheIdempotencyStore;
use Payum\Core\Middleware\LockingMiddleware;
use Payum\Core\Middleware\MiddlewareInterface;
use Symfony\Component\Lock\LockFactory;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Storage\CryptoStorageDecorator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;
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
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('payum.php');
        $loader->load(PayumVersion::supportsDependencyInjection() ? 'payum_v2.php' : 'payum_v1.php');
        $loader->load('commands.php');
        $loader->load('controller.php');
        $loader->load('form.php');

        if ($container->getParameter('kernel.debug')) {
            $loader->load('debug.php');
        }

        $this->loadStorages($config['storages'], $container);
        $this->loadSecurity($config['security'], $container);

        $this->loadCoreGateway($config['gateways']['core'] ?? [], $container);
        unset($config['gateways']['core']);

        $this->loadGateways($config['gateways'], $container);

        if (isset($config['dynamic_gateways'])) {
            $this->loadDynamicGateways($config['dynamic_gateways'], $container);
        }

        if ($config['locking']['enabled']) {
            $this->loadLocking($config['locking'], $container);
        }

        if (isset($config['idempotency'])) {
            $this->loadIdempotency($config['idempotency'], $container);
        }

        if (interface_exists(MiddlewareInterface::class)) {
            $container->registerForAutoconfiguration(MiddlewareInterface::class)->addTag('payum.middleware');
            $container->registerAttributeForAutoconfiguration(AsPayumMiddleware::class, static function (ChildDefinition $definition, AsPayumMiddleware $attribute): void {
                $definition->addTag('payum.middleware', ['priority' => $attribute->priority]);
            });
        }
    }

    protected function loadLocking(array $config, ContainerBuilder $container): void
    {
        if (false === PayumVersion::supportsDependencyInjection()) {
            throw new LogicException('"payum.locking" needs payum/core 2.0 or later.');
        }

        if (false === class_exists(LockFactory::class)) {
            throw new LogicException('"payum.locking" needs Symfony Lock. Run "composer require symfony/lock".');
        }

        $container->register('payum.middleware.locking', LockingMiddleware::class)
            ->setArguments([
                new Reference($config['lock_factory']),
                new Reference('payum.static_registry'),
                $config['timeout'],
                $config['ttl'],
            ])
            // no priority on the tag: the middleware's own 900 applies
            ->addTag('payum.middleware')
        ;
    }

    protected function loadIdempotency(array $config, ContainerBuilder $container): void
    {
        if (false === PayumVersion::supportsDependencyInjection()) {
            throw new LogicException('"payum.idempotency" needs payum/core 2.0 or later.');
        }

        if ($config['service']) {
            $container->setAlias('payum.idempotency_store', $config['service']);

            return;
        }

        if ($config['cache_pool']) {
            $container->register('payum.idempotency_store', CacheIdempotencyStore::class)
                ->setArguments([new Reference($config['cache_pool'])]);

            return;
        }

        $container->register('payum.idempotency_store', DbalIdempotencyStore::class)
            ->setArguments([new Reference($config['dbal_connection']), $config['table']]);
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
            if (isset($gatewayConfig['config'])) {
                $builder->addMethodCall('registerGateway', [$gatewayName, $this->createGatewayConfigDefinition($gatewayName, $gatewayConfig)]);

                continue;
            }

            $builder->addMethodCall('addGateway', [$gatewayName, $gatewayConfig]);
        }
    }

    /**
     * Arguments are passed to the config's constructor by name, so `%env()%` placeholders resolve at runtime.
     */
    private function createGatewayConfigDefinition(string $gatewayName, array $gatewayConfig): Definition
    {
        if (false === PayumVersion::supportsDependencyInjection()) {
            throw new LogicException(sprintf('The gateway "%s" sets "config", which needs payum/core 2.0 or later. Use "factory" with payum/core 1.x.', $gatewayName));
        }

        $unknown = array_diff(array_keys($gatewayConfig), ['config', 'arguments']);
        if ([] !== $unknown) {
            throw new InvalidArgumentException(sprintf(
                'The gateway "%s" sets "config", so it only takes "config" and "arguments", but %s given. Pass constructor values under "arguments".',
                $gatewayName,
                '"'.implode('", "', $unknown).'" '.(1 === count($unknown) ? 'was' : 'were')
            ));
        }

        $class = $gatewayConfig['config'];
        if (false === is_string($class) || false === is_a($class, GatewayConfig::class, true)) {
            throw new InvalidArgumentException(sprintf('The "config" of gateway "%s" must be a class implementing %s.', $gatewayName, GatewayConfig::class));
        }

        $arguments = [];
        foreach ($gatewayConfig['arguments'] ?? [] as $name => $value) {
            $arguments[is_string($name) && '$' !== $name[0] ? '$'.$name : $name] = $value;
        }

        return new Definition($class, $arguments);
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
        ];

        if (PayumVersion::supportsDependencyInjection()) {
            // The action is built by PayumCoreGatewayFactory, which takes the template from the
            // configuration of the gateway it builds rather than from a single service.
            $defaultConfig[FormFactoryInterface::class] = new Reference('form.factory');
            $defaultConfig[RequestStack::class] = new Reference('request_stack');
        } else {
            $defaultConfig['payum.action.obtain_credit_card'] = new Reference('payum.action.obtain_credit_card_builder');
        }

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

    private function createCypherDefinition(array $keys): ?Definition
    {
        if (isset($keys['defuse_secret_key'])) {
            return new Definition(DefuseCypher::class, [$keys['defuse_secret_key']]);
        }

        if (isset($keys['halite_key'])) {
            if (! class_exists(HaliteCypher::class)) {
                throw new LogicException('Encrypting gateway configs with "halite_key" needs payum/core 2.0 or newer.');
            }

            if (! class_exists(KeyFactory::class)) {
                throw new LogicException('Encrypting gateway configs with "halite_key" needs paragonie/halite. Run "composer require paragonie/halite".');
            }

            return new Definition(HaliteCypher::class, [$keys['halite_key']]);
        }

        return null;
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


        $encryption = $dynamicGatewaysConfig['encryption'] ?? [];
        if ($cypher = $this->createCypherDefinition($encryption)) {
            $container->setDefinition('payum.dynamic_gateways.cypher', $cypher);
            $container->register('payum.dynamic_gateways.encrypted_config_storage', CryptoStorageDecorator::class)
                ->setPublic(false)
                ->setDecoratedService('payum.dynamic_gateways.config_storage')
                ->addArgument(new Reference('payum.dynamic_gateways.encrypted_config_storage.inner'))
                ->addArgument(new Reference('payum.dynamic_gateways.cypher'))
            ;

            $previousCypher = null;
            if ($previous = $this->createCypherDefinition($encryption['previous'] ?? [])) {
                $container->setDefinition('payum.dynamic_gateways.previous_cypher', $previous);
                $previousCypher = new Reference('payum.dynamic_gateways.previous_cypher');
            }

            $container->register('payum.command.reencrypt_gateway_configs', ReencryptGatewayConfigsCommand::class)
                ->setArguments([
                    new Reference('payum.dynamic_gateways.encrypted_config_storage.inner'),
                    new Reference('payum.dynamic_gateways.cypher'),
                    $previousCypher,
                ])
                ->addTag('console.command')
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
            //throw new \LogicException('Not supported. Has to wait till Sonata Admin 4.x will be released.');

            if (false === class_exists(AbstractAdmin::class)) {
                throw new LogicException('Admin class does not exists. Did you install SonataAdmin bundle?');
            }

            $gatewayConfigAdmin =  new Definition(GatewayConfigAdmin::class, [
                null,
                $configClass,
                null
            ]);
            $gatewayConfigAdmin->addMethodCall('setGatewayFactoryRegistry', [new Reference('payum')]);

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
