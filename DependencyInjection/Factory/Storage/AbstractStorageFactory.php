<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

abstract class AbstractStorageFactory implements StorageFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $modelClass, array $config): string
    {
        $storageId = sprintf('payum.storage.%s', strtolower(str_replace(array('\\\\', '\\'), '_', $modelClass)));

        $storageDefinition = $this->createStorage($container, $modelClass, $config);
        $storageDefinition->setPublic(true);
        
        $container->setDefinition($storageId, $storageDefinition);

        return $storageId;
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
    }

    abstract protected function createStorage(ContainerBuilder $container, string $modelClass, array $config): Definition;
}
