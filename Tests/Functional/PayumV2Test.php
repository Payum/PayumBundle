<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Bundle\PayumBundle\Action\ObtainCreditCardAction;
use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Bundle\PayumBundle\Tests\Functional\Fixture\ExchangeRates;
use Payum\Bundle\PayumBundle\Tests\Functional\Fixture\TaggedCaptureAction;
use Payum\Bundle\PayumBundle\Tests\Functional\Fixture\StatusListener;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Gateway;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\Result\PaymentStatus;
use ReflectionObject;

/**
 * What the bundle adds on top of payum/core 2.0: a gateway built through the container, with the
 * actions of the bundle and the services the application tagged for it.
 */
class PayumV2Test extends WebTestCase
{
    protected function setUp(): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('Needs payum/core 2.0 or later.');
        }

        parent::setUp();
    }

    public function testShouldBuildAGatewayOfTheCoreFactory(): void
    {
        $this->assertInstanceOf(Gateway::class, $this->getPayum()->getGateway('diGateway'));
    }

    public function testShouldRegisterTheActionsOfTheBundleOnIt(): void
    {
        $actions = $this->actionsOf($this->getPayum()->getGateway('diGateway'));

        $this->assertContains(ObtainCreditCardAction::class, array_map('get_class', $actions));
    }

    public function testShouldRegisterTheHttpRequestActionOfTheBundleOnIt(): void
    {
        $actions = $this->actionsOf($this->getPayum()->getGateway('diGateway'));

        $this->assertContains(
            \Payum\Bundle\PayumBundle\Action\GetHttpRequestAction::class,
            array_map('get_class', $actions)
        );
    }

    public function testShouldRegisterAServiceTaggedForTheGatewayOnIt(): void
    {
        $actions = $this->actionsOf($this->getPayum()->getGateway('diGateway'));

        $this->assertContains(TaggedCaptureAction::class, array_map('get_class', $actions));
    }

    public function testShouldNotRegisterAServiceTaggedForTheGatewayOnAnotherOne(): void
    {
        $actions = $this->actionsOf($this->getPayum()->getGateway('fooGateway'));

        $this->assertNotContains(TaggedCaptureAction::class, array_map('get_class', $actions));
    }

    public function testShouldShareTheTaggedServicesOfTheApplicationWithPayum(): void
    {
        $container = static::getContainer()->get('payum.di.global_container');

        $this->assertTrue($container->has(ExchangeRates::class));
        $this->assertInstanceOf(ExchangeRates::class, $container->get(ExchangeRates::class));
        $this->assertContains(ExchangeRates::class, $container->getKnownEntryNames());
    }

    public function testShouldStillBuildTheGatewaysOfTheOtherFactories(): void
    {
        $payum = $this->getPayum();

        $this->assertInstanceOf(Gateway::class, $payum->getGateway('fooGateway'));
        $this->assertInstanceOf(Gateway::class, $payum->getGateway('barGateway'));
    }

    public function testShouldRunTheHandlersOfAGatewayRegisteredInTheBundleConfig(): void
    {
        $result = $this->getPayum()->getGateway('offlineFromConfig')->execute(CaptureCommand::forPayment(new Payment()));

        // OFFLINE_PAID is false, so a capture stays pending.
        $this->assertSame(PaymentStatus::Pending, $result->status);
    }

    public function testShouldRunTheHandlersOfAGatewayRegisteredByATaggedConfig(): void
    {
        $result = $this->getPayum()->getGateway('offlineFromTag')->execute(CaptureCommand::forPayment(new Payment()));

        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testShouldDispatchPayumEventsToTheListenersOfTheApplication(): void
    {
        StatusListener::$events = [];

        $this->getPayum()->getGateway('offlineFromTag')->execute(CaptureCommand::forPayment(new Payment()));

        $this->assertCount(1, StatusListener::$events);
        $this->assertInstanceOf(StatusChanged::class, StatusListener::$events[0]);
        $this->assertSame(PaymentStatus::Captured, StatusListener::$events[0]->to);
    }

    private function getPayum(): Payum
    {
        return static::getContainer()->get('payum');
    }

    /**
     * @return list<object>
     */
    private function actionsOf(Gateway $gateway): array
    {
        $reflected = (new ReflectionObject($gateway))->getProperty('actions');
        $reflected->setAccessible(true);

        return array_values($reflected->getValue($gateway));
    }
}
