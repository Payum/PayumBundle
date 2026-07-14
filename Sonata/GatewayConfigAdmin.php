<?php
namespace Payum\Bundle\PayumBundle\Sonata;

use Payum\Bundle\PayumBundle\Form\Type\GatewayFactoriesChoiceType;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Payum\Bundle\PayumBundle\Form\Type\GatewayConfigType;
use Symfony\Component\PropertyAccess\PropertyAccess;

class GatewayConfigAdmin extends AbstractAdmin
{
    protected ?CypherInterface $cypher;

    protected GatewayFactoryRegistryInterface $registry;
    
    public function setCypher(CypherInterface $cypher): void
    {
        $this->cypher = $cypher;
    }
    
    public function setGatewayFactoryRegistry(GatewayFactoryRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form): void
    {

        $form
            ->with('General', ['class' => 'col-md-4'])->end()
            ->with('Configuration', ['class' => 'col-md-8'])->end()
        ;

        $form
            ->with('General')
            ->add('gatewayName')
            ->add('factoryName', GatewayFactoriesChoiceType::class, [
                'disabled' => (bool) $this->getSubject() && null !== $this->getSubject()->getId(),
            ])
            ->end()
        ;

        if ($this->getSubject() && $this->getSubject()->getId()) {
            $this->buildCredentials($form, $this->getSubject());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('gatewayName')
            ->add('factoryName')
            ->add('config', 'array')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object): void
    {
        parent::preUpdate($object);

        if ($this->cypher && $object instanceof CryptedInterface) {
            $object->encrypt($this->cypher);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object): void
    {
        parent::prePersist($object);

        if ($this->cypher && $object instanceof CryptedInterface) {
            $object->encrypt($this->cypher);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function alterObject(object $object): void
    {
        if ($this->cypher && $object instanceof CryptedInterface) {
            $object->decrypt($this->cypher);
        }
    }

    public function buildCredentials(FormMapper $form, object $object): void
    {
        /** @var array $data */
        $data = $object;


        $propertyPath = is_array($data) ? '[factoryName]' : 'factoryName';
        $factoryName = PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);
        if (empty($factoryName)) {
            return;
        }

        $gatewayFactory = $this->registry->getGatewayFactory($factoryName);
        $config = $gatewayFactory->createConfig();

        if (isset($config['payum.gateway_config_type'])) {
            $form
                ->with('Configuration')
                ->add('config', $config['payum.gateway_config_type'], [
                    'label' => false,
                ])
                ->end()
            ;

            return;
        }

        $form->add('config', FormType::class);
        $configForm = $form->get('config');

        $propertyPath = is_array($data) ? '[config]' : 'config';
        $firstTime = ! PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);

        foreach ($config['payum.default_options'] as $name => $value) {
            $propertyPath = is_array($data) ? "[config][{$name}]" : "config[{$name}]";
            if ($firstTime) {
                PropertyAccess::createPropertyAccessor()->setValue($data, $propertyPath, $value);
            }

            $type = is_bool($value) ? CheckboxType::class : TextType::class;

            $options = [];
            $options['required'] = in_array($name, $config['payum.required_options']);

            $configForm->add($name, $type, $options);
        }
    }
}
