<?php

namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface StorageFactoryInterface
{
    /**
     * @param ContainerBuilder $container
     * @param string           $contextName
     * @param string           $modelClass
     * @param string           $paymentId
     * @param array            $config
     *
     * @return string The context serviceId
     */
    public function create(ContainerBuilder $container, $contextName, $modelClass, $paymentId, array $config);

    /**
     * The storage name, 
     * For example filesystem, doctrine, propel etc.
     * 
     * @return string
     */
    public function getName();

    /**
     * @param ArrayNodeDefinition $builder
     */
    public function addConfiguration(ArrayNodeDefinition $builder);
}
