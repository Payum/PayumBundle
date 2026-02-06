<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;

class Propel1StorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return "propel1";
    }

    protected function createStorage(ContainerBuilder $container, string $modelClass, array $config): ChildDefinition
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
        $loader->load('propel1.php');
        
        $storage = new ChildDefinition('payum.storage.propel1');
        $storage->setPublic(true);
        $storage->replaceArgument(0, $modelClass);
        
        return $storage;
    }
}
