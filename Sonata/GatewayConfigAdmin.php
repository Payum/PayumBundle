<?php
namespace Payum\Bundle\PayumBundle\Sonata;

use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Payum\Bundle\PayumBundle\Form\Type\GatewayConfigType;

class GatewayConfigAdmin extends AbstractAdmin
{
    protected FormFactoryInterface $formFactory;
    protected ?CypherInterface $cypher;

    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    public function setCypher(CypherInterface $cypher): void
    {
        $this->cypher = $cypher;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form->reorder(array()); //hack!
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
    public function getObject($id)
    {
        $object = parent::getObject($id);

        if ($this->cypher && $object instanceof CryptedInterface) {
            $object->decrypt($this->cypher);
        }

        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormBuilder(): FormBuilderInterface
    {
        $formBuilder = $this->formFactory->createBuilder(GatewayConfigType::class, $this->getSubject(), array(
            'data_class' => get_class($this->getSubject()),
        ));

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }
}
