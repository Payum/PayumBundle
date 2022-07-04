<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

interface StorageFactoryInterface
{
    /**
     * @param string $modelClass
     * @return string The payment serviceId
     */
    public function create(ContainerBuilder $container, $modelClass, array $config);

    /**
     * The storage name,
     * For example filesystem, doctrine, propel etc.
     */
    public function getName();

    /**
     * @return void
     */
    public function addConfiguration(ArrayNodeDefinition $builder);
}
