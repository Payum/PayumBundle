<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Form\Type;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Bundle\PayumBundle\Form\Type\CreditCardExpirationDateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;

class CreditCardExpirationDateTypeTest extends WebTestCase
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

        $form = $this->formFactory->create(CreditCardExpirationDateType::class);
        $view = $form->createView();

        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertInstanceOf(FormView::class, $view);
    }

    /**
     * @test
     */
    public function shouldAllowSubmitExpireDateAsChoice(): void
    {
        $form = $this->formFactory->create(CreditCardExpirationDateType::class, null, array(
            'widget' => 'choice',
            'input' => 'datetime',
            'csrf_protection' => false,
        ));

        $year = (int) date('Y') + 2;

        $form->submit(array(
            'day' => 1,
            'month' => 1,
            'year' => $year,
        ));

        $this->assertTrue($form->isValid(), $form->getErrors(true, false));

        $data = $form->getData();
        $this->assertInstanceOf(\DateTime::class, $data);
        $this->assertEquals($year.'-01-01', $data->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function shouldHideDaySelectAndSetFirstDayFromChoiceAsValue(): void
    {
        $form = $this->formFactory->create(CreditCardExpirationDateType::class, null, array(
            'widget' => 'choice',
            'input' => 'datetime',
            'csrf_protection' => false,
        ));

        $view = $form->createView();

        $this->assertEquals(array('style' => 'display:none'), $view['day']->vars['attr']);
        $this->assertEquals(1, $view['day']->vars['value']);
    }

    /**
     * @test
     */
    public function shouldHideDaySelectAndSetDayFromGivenDateTimeAsValue(): void
    {
        $date = new \DateTime('2020-01-10');

        $form = $this->formFactory->create(CreditCardExpirationDateType::class, $date, array(
            'widget' => 'choice',
            'input' => 'datetime',
            'csrf_protection' => false,
        ));

        $view = $form->createView();

        $this->assertEquals(array('style' => 'display:none'), $view['day']->vars['attr']);
        $this->assertEquals(10, $view['day']->vars['value']);
    }
}
