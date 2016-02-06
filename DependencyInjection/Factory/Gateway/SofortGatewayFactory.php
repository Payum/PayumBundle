<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class SofortGatewayFactory extends AbstractGatewayFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sofort';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder->children()
            ->scalarNode('config_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('abort_url')->isRequired()->cannotBeEmpty()->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function getPayumgatewayFactoryClass()
    {
        return SofortGatewayFactory::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/sofort';
    }
}