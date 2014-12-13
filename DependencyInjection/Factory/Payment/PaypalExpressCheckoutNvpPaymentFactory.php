<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Reference;

class PaypalExpressCheckoutNvpPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paypal_express_checkout_nvp';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('signature')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     *
     * @return Definition
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            throw new RuntimeException('Cannot find paypal express checkout payment factory class. Have you either installed payum/paypal-express-checkout-nvp or payum/payum package?');
        }

        $factory = new Definition();
        $factory->setClass('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory');
        $factory->addArgument(new Reference('payum.builder_default'));
        $factoryId = 'payum.context.'.$contextName.'.factory';
        $container->setDefinition($factoryId, $factory);

        $payment = new Definition();
        $payment->setClass('Payum\Core\PaymentInterface');
        $payment->setFactoryService($factoryId);
        $payment->setFactoryMethod('create');
        $payment->addArgument($config);

        return $payment;
    }
}