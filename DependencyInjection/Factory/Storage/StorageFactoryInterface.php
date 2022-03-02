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
    function create(ContainerBuilder $container, $modelClass, array $config);

    /**
     * The storage name,
     * For example filesystem, doctrine, propel etc.
     *
     * @return string
     */
    function getName();

    /**
     * @return void
     */
    function addConfiguration(ArrayNodeDefinition $builder);
}
