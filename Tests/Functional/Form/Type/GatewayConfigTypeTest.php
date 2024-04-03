<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Form\Type;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Bundle\PayumBundle\Form\Type\GatewayConfigType;
use Payum\Core\Model\GatewayConfig;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;

class GatewayConfigTypeTest extends WebTestCase
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

        $form = $this->formFactory->create(GatewayConfigType::class, null, [
            'data_class' => GatewayConfig::class,
        ]);

        $view = $form->createView();

        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertInstanceOf(FormView::class, $view);
    }
}
