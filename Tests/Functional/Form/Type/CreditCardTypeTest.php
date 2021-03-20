<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Form\Type;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Bridge\Symfony\Form\Type\CreditCardType;
use Payum\Core\Model\CreditCardInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class CreditCardTypeTest extends WebTestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formFactory = static::$container->get('form.factory');
    }

    /**
     * @test
     */
    public function couldBeCreatedByFormFactory()
    {
        $form = $this->formFactory->create(CreditCardType::class);
        $view = $form->createView();

        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertInstanceOf(FormView::class, $view);
    }

    /**
     * @test
     */
    public function shouldSubmitDataCorrectly()
    {
        $form = $this->formFactory->create(CreditCardType::class, null, array(
            'csrf_protection' => false,
        ));

        $year = date('Y') + 2;

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => $year,
            ),
        ));

        $this->assertTrue($form->isValid(), $form->getErrors(true, false));

        /** @var CreditCardInterface $card */
        $card = $form->getData();

        $this->assertInstanceOf('Payum\Core\Model\CreditCardInterface', $card);

        $this->assertEquals('John Doe', $card->getHolder());
        $this->assertEquals('4111111111111111', $card->getNumber());
        $this->assertEquals('123', $card->getSecurityCode());
        $this->assertEquals($year.'-10-31', $card->getExpireAt()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function shouldRequireHolderNotBlank()
    {
        $form = $this->formFactory->create(CreditCardType::class, null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => '',
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('holder')->isValid());
    }

    /**
     * @test
     */
    public function shouldRequireNumberNotBlank()
    {
        $form = $this->formFactory->create(CreditCardType::class, null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '',
            'securityCode' => '123',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('number')->isValid());
    }

    /**
     * @test
     */
    public function shouldNumberPassLuchValidation()
    {
        $form = $this->formFactory->create(CreditCardType::class, null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '1234',
            'securityCode' => '123',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('number')->isValid());
    }

    /**
     * @test
     */
    public function shouldRequireSecurityCodeNotBlank()
    {
        $form = $this->formFactory->create(CreditCardType::class, null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '4111111111111111',
            'securityCode' => '',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('securityCode')->isValid());
    }

    /**
     * @test
     */
    public function shouldRequireExpireAtNotBlank()
    {
        $form = $this->formFactory->create(CreditCardType::class, null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '4111111111111111',
            'securityCode' => '',
            'expireAt' => array(
                'day' => '',
                'month' => '',
                'year' => '',
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('expireAt')->isValid());
    }

    /**
     * @test
     */
    public function shouldRequireExpireAtInFuture()
    {
        $form = $this->formFactory->create(CreditCardType::class, null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '4111111111111111',
            'securityCode' => '',
            'expireAt' => array(
                'day' => '1',
                'month' => '1',
                'year' => '1970',
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('expireAt')->isValid());
    }
}
