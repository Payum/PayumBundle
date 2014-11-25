<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

class BitcoindPaymentFactory extends AbstractPaymentFactory implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Bitcoind\PaymentFactory')) {
            throw new RuntimeException('Cannot find bitcoind payment factory class. Have you installed payum/bitcoind package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('bitcoind.xml');

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bitcoind';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('rpcuser')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('rpcpassword')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('rpchost')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('rpcport')->isRequired()->cannotBeEmpty()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', array(
            'paths' => array_flip(array_filter(array(
                'PayumCore' => TwigFactory::guessViewsPath('Payum\Core\Payment'),
                'PayumBitcoind' => TwigFactory::guessViewsPath('Payum\Bitcoind\PaymentFactory'),
            )))
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
    }
}