<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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

        $builder = new Definition();
        $builder->setClass('Payum\Core\PaymentBuilderInterface');
        $builder->setFactoryClass('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory');
        $builder->setFactoryMethod('createBuilder');
        $builder->setArguments(array($config));
        $builder->setPublic(false);

        $builderId = 'payum.context.'.$contextName.'.builder';

        $container->setDefinition($builderId, $builder);

        $payment = new Definition();
        $payment->setClass('Payum\Core\PaymentInterface');
        $payment->setFactoryService($builderId);
        $payment->setFactoryMethod('getPayment');

        return $payment;
    }
}