<?php

namespace Payum\Bundle\PayumBundle\Tests\Builder;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Bundle\PayumBundle\Action\ObtainCreditCardAction;
use Payum\Bundle\PayumBundle\Builder\ObtainCreditCardActionBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ObtainCreditCardActionBuilderTest extends TestCase
{
    public function testShouldBuildObtainCreditCardWithGivenTemplate(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $requestStack = $this->createMock(RequestStack::class);

        $builder = new ObtainCreditCardActionBuilder($formFactory, $requestStack);

        $action = $builder->build(new ArrayObject([
            'payum.template.obtain_credit_card' => 'obtain_credit_card_template',
        ]));

        $this->assertInstanceOf(ObtainCreditCardAction::class, $action);
    }

    public function testAllowUseBuilderAsAsFunction(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $requestStack = $this->createMock(RequestStack::class);

        $builder = new ObtainCreditCardActionBuilder($formFactory, $requestStack);

        $action = $builder(new ArrayObject([
            'payum.template.obtain_credit_card' => 'obtain_credit_card_template',
        ]));

        $this->assertInstanceOf(ObtainCreditCardAction::class, $action);
    }
}
