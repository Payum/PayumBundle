<?php

namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface PaymentFactoryInterface
{
    /**
     * @param ContainerBuilder $container
     * @param string           $contextName
     * @param array            $config
     * 
     * @return string The context serviceId
     */
    public function create(ContainerBuilder $container, $contextName, array $config);

    /**
     * The payment name, 
     * For example paypal_express_checkout_nvp or authorize_net_aim
     * 
     * @return string
     */
    public function getName();

    /**
     * @param ArrayNodeDefinition $builder
     */
    public function addConfiguration(ArrayNodeDefinition $builder);
}
