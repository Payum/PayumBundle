<?php
namespace Payum\Bundle\PayumBundle\Sonata;

use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormFactoryInterface;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;

class GatewayConfigAdmin extends AbstractAdmin
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var CypherInterface|null
     */
    protected $cypher;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param CypherInterface $cypher
     */
    public function setCypher(CypherInterface $cypher)
    {
        $this->cypher = $cypher;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->reorder(array()); //hack!
    }

    /**
     * {@inheritDoc}
     */
    protected function configureListFields(ListMapper $list)
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
    public function preUpdate($object)
    {
        parent::preUpdate($object);

        if ($this->cypher && $object instanceof CryptedInterface) {
            $object->encrypt($this->cypher);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
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
    public function getFormBuilder()
    {
        $formBuilder = $this->formFactory->createBuilder(GatewayConfigType::class, $this->getSubject(), array(
            'data_class' => get_class($this->getSubject()),
        ));

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }
}