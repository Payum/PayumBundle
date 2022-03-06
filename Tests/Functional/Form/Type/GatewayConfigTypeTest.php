<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Form\Type;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use Payum\Core\Model\GatewayConfig;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

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
        $this->markTestIncomplete('Symfony6 needs now a session. Rewrite in request-context');

        $form = $this->formFactory->create(GatewayConfigType::class, null, [
            'data_class' => GatewayConfig::class,
        ]);

        $view = $form->createView();

        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertInstanceOf(FormView::class, $view);
    }
}
