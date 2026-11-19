<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Bundle\PayumBundle\Action\ObtainCreditCardAction;
use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Bundle\PayumBundle\Tests\Functional\Fixture\AttributedMiddleware;
use Payum\Bundle\PayumBundle\Tests\Functional\Fixture\ExchangeRates;
use Payum\Bundle\PayumBundle\Tests\Functional\Fixture\RecordingMiddleware;
use Payum\Bundle\PayumBundle\Tests\Functional\Fixture\TaggedCaptureAction;
use Payum\Bundle\PayumBundle\Tests\Functional\Fixture\StatusListener;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Gateway;
use Payum\Core\Idempotency\CacheIdempotencyStore;
use Payum\Core\Idempotency\IdempotencyStoreInterface;
use Payum\Core\Middleware\SubjectIdentity;
use Payum\Core\Security\TokenInterface;
use Psr\Http\Client\ClientInterface;
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

    public function testShouldRunTheMiddlewareOfTheApplicationWithTheRequestSymfonyServes(): void
    {
        RecordingMiddleware::$requests = [];
        $token = $this->createCaptureToken($this->storePayment());

        $this->client->request('GET', $token->getTargetUrl(), ['foo' => 'bar']);

        $this->assertStringStartsWith('http://localhost/after?payum_token=', (string) $this->client->getResponse()->headers->get('Location'));
        $this->assertNotEmpty(RecordingMiddleware::$requests);
        $this->assertSame(parse_url($token->getTargetUrl(), PHP_URL_PATH), RecordingMiddleware::$requests[0]['path']);
        $this->assertSame('bar', RecordingMiddleware::$requests[0]['query']['foo'] ?? null);
    }

    public function testShouldGiveHandlersEachRequestWhenTheKernelServesSeveral(): void
    {
        // what a worker runtime does: the same services serve one request after the other
        $this->client->disableReboot();
        $first = $this->createCaptureToken($this->storePayment());
        $second = $this->createCaptureToken($this->storePayment());

        RecordingMiddleware::$requests = [];
        $this->client->request('GET', $first->getTargetUrl());
        $this->client->request('GET', $second->getTargetUrl(), ['second' => '1']);

        $last = end(RecordingMiddleware::$requests);
        $this->assertSame(parse_url($second->getTargetUrl(), PHP_URL_PATH), $last['path']);
        $this->assertSame('1', $last['query']['second'] ?? null);
    }

    public function testShouldRunAMiddlewareRegisteredByItsAttributeOncePerCommand(): void
    {
        RecordingMiddleware::$requests = [];
        AttributedMiddleware::$calls = 0;

        $this->getPayum()->getGateway('offlineFromTag')->execute(CaptureCommand::forPayment(new Payment()));

        $this->assertGreaterThan(0, AttributedMiddleware::$calls);
        $this->assertSame(count(RecordingMiddleware::$requests), AttributedMiddleware::$calls);
    }

    public function testShouldSendTheCustomerToTheAfterUrlWhileAnotherRequestHoldsThePayment(): void
    {
        $payment = $this->storePayment();
        $token = $this->createCaptureToken($payment);

        $lock = static::getContainer()->get('lock.factory')->createLock(
            'payum:' . SubjectIdentity::of(CaptureCommand::forPayment($payment), $this->getPayum())
        );
        $this->assertTrue($lock->acquire());

        try {
            $this->client->request('GET', $token->getTargetUrl());
        } finally {
            $lock->release();
        }

        $this->assertStringStartsWith('http://localhost/after?payum_token=', (string) $this->client->getResponse()->headers->get('Location'));
        // left for the request holding the payment to invalidate
        $this->assertNotNull($this->getPayum()->getTokenStorage()->find($token->getHash()));
    }

    public function testShouldShareTheServicesOfTheFrameworkWithPayum(): void
    {
        $container = static::getContainer()->get('payum.di.global_container');

        $this->assertInstanceOf(CacheIdempotencyStore::class, $container->get(IdempotencyStoreInterface::class));
        $this->assertTrue($container->has(ClientInterface::class));
        $this->assertTrue($container->has(\Symfony\Component\Lock\LockFactory::class));
    }

    private function storePayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid('order-', true));
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(100);
        $this->getPayum()->getStorage(Payment::class)->update($payment);

        return $payment;
    }

    private function createCaptureToken(Payment $payment): TokenInterface
    {
        return $this->getPayum()->getTokenFactory()->createCaptureToken('offlineFromTag', $payment, 'http://localhost/after');
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
