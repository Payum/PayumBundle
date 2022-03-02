<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class FilesystemStorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'filesystem';
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('storage_dir')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('id_property')->defaultValue(null)->end()
        ->end();
    }

    protected function createStorage(ContainerBuilder $container, string $modelClass, array $config): ChildDefinition
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
        $loader->load('filesystem.xml');

        $storage = new ChildDefinition('payum.storage.filesystem.prototype');
        $storage->setPublic(true);
        $storage->replaceArgument(0, $config['storage_dir']);
        $storage->replaceArgument(1, $modelClass);
        $storage->replaceArgument(2, $config['id_property']);

        return $storage;
    }
}
