<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Form\Type;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Model\CreditCardInterface;
use Symfony\Component\Form\FormFactoryInterface;

class ContextChoiceTypeTest extends WebTestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->formFactory = $this->container->get('form.factory');
    }

    /**
     * @test
     */
    public function couldBeCreatedByFormFactory()
    {
        $form = $this->formFactory->create('payum_context_choice');
        $view = $form->createView();

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $form);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $view);
    }

    /**
     * @test
     */
    public function shouldSubmitDataCorrectly()
    {
        $form = $this->formFactory->create('payum_context_choice', null, array(
            'csrf_protection' => false
        ));

        $form->submit('offline');

        $this->assertTrue($form->isValid(), $form->getErrorsAsString());

        $this->assertEquals('offline', $form->getData());
    }

    /**
     * @test
     */
    public function shouldNotBeValidIfValueNotFromChoiceList()
    {
        $form = $this->formFactory->create('payum_context_choice', null, array(
            'csrf_protection' => false
        ));

        $form->submit('notExistContext');

        $this->assertFalse($form->isValid());
    }
}