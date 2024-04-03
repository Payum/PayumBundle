<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Form\Type;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Bundle\PayumBundle\Form\Type\CreditCardType;
use Payum\Core\Model\CreditCardInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;

class CreditCardTypeTest extends WebTestCase
{
    protected ?FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formFactory = static::getContainer()->get('form.factory');
    }

    /**
     * @test
     */
    public function couldBeCreatedByFormFactory(): void
    {
        if (Kernel::MAJOR_VERSION >= 6) {
            /** @var RequestStack $requestStack */
            $requestStack = self::getContainer()->get(RequestStack::class);
            $request = Request::createFromGlobals();
            $request->setSession(new Session(new MockArraySessionStorage()));
            $requestStack->push($request);
        }

        $form = $this->formFactory->create(CreditCardType::class);
        $view = $form->createView();

        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertInstanceOf(FormView::class, $view);
    }

    /**
     * @test
     */
    public function shouldSubmitDataCorrectly(): void
    {
        $form = $this->formFactory->create(CreditCardType::class, null, array(
            'csrf_protection' => false,
        ));

        $year = (int) date('Y') + 2;

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
    public function shouldRequireHolderNotBlank(): void
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
    public function shouldRequireNumberNotBlank(): void
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
    public function shouldNumberPassLuchValidation(): void
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
    public function shouldRequireSecurityCodeNotBlank(): void
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
    public function shouldRequireExpireAtNotBlank(): void
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
    public function shouldRequireExpireAtInFuture(): void
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
