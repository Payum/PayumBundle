<?php
namespace Payum\Bundle\PayumBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContextChoiceType extends AbstractType
{
    /**
     * @var string[]
     */
    protected $contextNames;

    /**
     * @param string[] $contextNames
     */
    public function __construct(array $contextNames)
    {
        $this->contextNames = $contextNames;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = array();
        foreach ($this->contextNames as $name) {
            $choices[$name] = 'context.'.$name;
        }

        $resolver->setDefaults(array(
            'choices' => $choices,
            'translation_domain' => 'PayumBundle',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'payum_context_choice';
    }
}