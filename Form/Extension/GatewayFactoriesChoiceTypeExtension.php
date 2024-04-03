<?php
namespace Payum\Bundle\PayumBundle\Form\Extension;

use Payum\Bundle\PayumBundle\Form\Type\GatewayFactoriesChoiceType;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayFactoriesChoiceTypeExtension extends AbstractTypeExtension
{
    private GatewayFactoryRegistryInterface $gatewayFactoryRegistry;

    public function __construct(GatewayFactoryRegistryInterface $gatewayFactoryRegistry)
    {
        $this->gatewayFactoryRegistry = $gatewayFactoryRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
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

    public function getExtendedType(): string
    {
        return GatewayFactoriesChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [GatewayFactoriesChoiceType::class];
    }
}
