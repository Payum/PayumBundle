<?php
namespace Payum\Bundle\PayumBundle\Form\Extension;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Exception;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayFactoriesChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * @var GatewayFactoryRegistryInterface
     */
    private $gatewayFactoryRegistry;

    /**
     * @param GatewayFactoryRegistryInterface $gatewayFactoryRegistry
     */
    public function __construct(GatewayFactoryRegistryInterface $gatewayFactoryRegistry)
    {
        $this->gatewayFactoryRegistry = $gatewayFactoryRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $options = $resolver->getDefinedOptions();
        if (empty($options['choices'])) {
            $choices = [];
            foreach ($this->gatewayFactoryRegistry->getGatewayFactories() as $name => $factory) {
                $choices['form.choice.'.$name] = $name;
            }

            $resolver->setDefaults([
                'choices' => $choices,
                'choice_translation_domain' => 'PayumBundle',
                'translation_domain' => 'PayumBundle',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return GatewayFactoriesChoiceType::class;
    }
}
