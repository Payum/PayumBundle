<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class DoctrineStorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'doctrine';
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        parent::addConfiguration($builder);
        
        $builder
            ->beforeNormalization()->ifString()->then(function($v) {
                return array('driver' => $v);
            })->end()
            ->children()
                ->scalarNode('driver')->isRequired()->cannotBeEmpty()->end()
            ->end();
    }

    protected function createStorage(ContainerBuilder $container, string $modelClass, array $config): ChildDefinition
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
        $loader->load('doctrine.'.$config['driver'].'.xml');

        $storage = new ChildDefinition(sprintf('payum.storage.doctrine.%s', $config['driver']));
        $storage->setPublic(true);
        $storage->replaceArgument(1, $modelClass);
        
        return $storage;
    }
}
