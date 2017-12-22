<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomStorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'custom';
    }

    /**
     * {@inheritdoc}
     */
    protected function createStorage(ContainerBuilder $container, $modelClass, array $config)
    {
        return new ChildDefinition($config['service']);
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder
            ->beforeNormalization()->ifString()->then(function($v) {
                return array('service' => $v);
            })->end()
            ->children()
                ->scalarNode('service')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}
