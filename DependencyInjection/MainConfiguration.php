<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Core\Exception\LogicException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class MainConfiguration implements ConfigurationInterface
{
    /**
     * @var StorageFactoryInterface[]
     */
    protected $storageFactories = array();

    /**
     * @param StorageFactoryInterface[] $storageFactories
     */
    public function __construct(array $storageFactories)
    {
        foreach ($storageFactories as $storageFactory) {
            $this->storageFactories[$storageFactory->getName()] = $storageFactory;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $rootNode = $tb->root('payum');

        $securityNode = $rootNode->children()
            ->arrayNode('security')->isRequired()
        ;
        $this->addSecuritySection($securityNode);

        $dynamicGatewaysNode = $rootNode->children()
            ->arrayNode('dynamic_gateways')
        ;
        $this->addDynamicGatewaysSection($dynamicGatewaysNode);

        $rootNode
            ->children()
            ->arrayNode('gateways')
                ->useAttributeAsKey('name')
                ->prototype('variable')
                ->treatNullLike([])
        ;
        
        $this->addStoragesSection($rootNode);

        return $tb;
    }

    /**
     * @param ArrayNodeDefinition $rootPrototypeNode
     */
    protected function addStoragesSection(ArrayNodeDefinition $rootPrototypeNode)
    {
        $storageNode = $rootPrototypeNode->children()
                ->arrayNode('storages')
                ->validate()
                    ->ifTrue(function($v) {
                        $storages = $v;
                        unset($storages['extension']);

                        foreach($storages as $key => $value) {
                            if (false == class_exists($key)) {
                                throw new LogicException(sprintf(
                                    'The storage entry must be a valid model class. It is set %s',
                                    $key
                                ));
                            }
                        }
                    
                        return false;
                    })
                    ->thenInvalid('A message')
                ->end()
                ->useAttributeAsKey('key')
                ->prototype('array')
        ;

        $storageNode
            ->validate()
                ->ifTrue(function($v) {
                    $storages = $v;
                    unset($storages['extension']);

                    if (count($storages) == 0) {
                        throw new LogicException('At least one storage must be configured.');
                    }
                    if (count($storages) > 1) {
                        throw new LogicException('Only one storage per entry could be selected');
                    }
                    
                    return false;
                })
                ->thenInvalid('A message')
            ->end()
        ;

        $storageNode->children()
            ->arrayNode('extension')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('all')->defaultValue(true)->end()
                    ->arrayNode('gateways')
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')
                    ->end()->end()
                    ->arrayNode('factories')
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')
                    ->end()->end()
                ->end()
            ->end()
        ->end();
        
        foreach ($this->storageFactories as $factory) {
            $factory->addConfiguration(
                $storageNode->children()->arrayNode($factory->getName())
            );
        }
    }

    /**
     * @param ArrayNodeDefinition $securityNode
     */
    protected function addSecuritySection(ArrayNodeDefinition $securityNode)
    {
        $storageNode = $securityNode->children()
            ->arrayNode('token_storage')
            ->isRequired()
            ->validate()
            ->ifTrue(function($v) {
                foreach($v as $key => $value) {
                    if (false == class_exists($key)) {
                        throw new LogicException(sprintf(
                            'The storage entry must be a valid model class. It is set %s',
                            $key
                        ));
                    }

                    $rc = new \ReflectionClass($key);
                    if (false == $rc->implementsInterface('Payum\Core\Security\TokenInterface')) {
                        throw new LogicException('The token class must implement `Payum\Core\Security\TokenInterface` interface');
                    }

                    if (count($v) > 1) {
                        throw new LogicException('Only one token storage could be configured.');
                    }
                }

                return false;
            })
            ->thenInvalid('A message')
            ->end()
            ->useAttributeAsKey('key')
            ->prototype('array')
        ;

        $storageNode
            ->validate()
            ->ifTrue(function($v) {
                if (count($v) == 0) {
                    throw new LogicException('At least one storage must be configured.');
                }
                if (count($v) > 1) {
                    throw new LogicException('Only one storage per entry could be selected');
                }

                return false;
            })
            ->thenInvalid('A message')
            ->end()
        ;

        foreach ($this->storageFactories as $factory) {
            $factory->addConfiguration(
                $storageNode->children()->arrayNode($factory->getName())
            );
        }
    }

    /**
     * @param ArrayNodeDefinition $dynamicGatewaysNode
     */
    protected function addDynamicGatewaysSection(ArrayNodeDefinition $dynamicGatewaysNode)
    {
        $dynamicGatewaysNode->children()
            ->booleanNode('sonata_admin')->defaultFalse()
        ;

        $storageNode = $dynamicGatewaysNode->children()
            ->arrayNode('config_storage')
            ->isRequired()
            ->validate()
            ->ifTrue(function($v) {
                foreach($v as $key => $value) {
                    if (false == class_exists($key)) {
                        throw new LogicException(sprintf(
                            'The storage entry must be a valid model class. It is set %s',
                            $key
                        ));
                    }

                    $rc = new \ReflectionClass($key);
                    if (false == $rc->implementsInterface('Payum\Core\Model\GatewayConfigInterface')) {
                        throw new LogicException('The config class must implement `Payum\Core\Model\GatewayConfigInterface` interface');
                    }

                    if (count($v) > 1) {
                        throw new LogicException('Only one config storage could be configured.');
                    }
                }

                return false;
            })
            ->thenInvalid('A message')
            ->end()
            ->useAttributeAsKey('key')
            ->prototype('array')
        ;

        $storageNode
            ->validate()
            ->ifTrue(function($v) {
                if (count($v) == 0) {
                    throw new LogicException('At least one storage must be configured.');
                }
                if (count($v) > 1) {
                    throw new LogicException('Only one storage per entry could be selected');
                }

                return false;
            })
            ->thenInvalid('A message')
            ->end()
        ;

        $dynamicGatewaysNode->children()
            ->arrayNode('encryption')
                ->children()
                    ->scalarNode('defuse_secret_key')->cannotBeEmpty()->end()
        ;

        foreach ($this->storageFactories as $factory) {
            $factory->addConfiguration(
                $storageNode->children()->arrayNode($factory->getName())
            );
        }
    }
}