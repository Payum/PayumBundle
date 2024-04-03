<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Bundle\PayumBundle\Security\HttpRequestVerifier;
use Payum\Bundle\PayumBundle\Security\TokenFactory;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Storage\StorageInterface;
use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;
use Payum\Stripe\StripeJsGatewayFactory;

class PayumTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $this->assertInstanceOf(Payum::class, $payum);
    }

    public function testShouldReturnHttpRequestVerifyRequest(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $this->assertInstanceOf(HttpRequestVerifier::class, $payum->getHttpRequestVerifier());
    }

    public function testShouldReturnTokenFactory(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $tokenFactory = $payum->getTokenFactory();
        $this->assertInstanceOf(GenericTokenFactory::class, $tokenFactory);

        $reflectedConstraint = (new \ReflectionObject($tokenFactory))->getProperty('tokenFactory');
        $reflectedConstraint->setAccessible(true);
        $constraint = $reflectedConstraint->getValue($tokenFactory);
        $this->assertInstanceOf(TokenFactory::class, $constraint);
    }

    public function testShouldReturnTokenStorage(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $storage = $payum->getTokenStorage();
        $this->assertInstanceOf(StorageInterface::class, $storage);
    }

    public function testShouldReturnStorages(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $storages = $payum->getStorages();
        $this->assertIsArray($storages);
        $this->assertCount(1, $storages);
    }

    public function testShouldReturnGateways(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $gateways = $payum->getGateways();
        $this->assertIsArray($gateways);
        $this->assertCount(2, $gateways);
    }

    public function testShouldReturnGatewaysFactories(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $factories = $payum->getGatewayFactories();
        $this->assertIsArray($factories);
        $this->assertGreaterThan(5, count($factories));
    }

    public function testShouldReturnGatewayFactory(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $this->assertInstanceOf(PaypalExpressCheckoutGatewayFactory::class, $payum->getGatewayFactory('paypal_express_checkout'));
        $this->assertInstanceOf(StripeJsGatewayFactory::class, $payum->getGatewayFactory('stripe_js'));
    }

    public function testShouldReturnGateway(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $this->assertInstanceOf(GatewayInterface::class, $payum->getGateway('fooGateway'));
        $this->assertInstanceOf(GatewayInterface::class, $payum->getGateway('barGateway'));
    }

    public function testShouldReturnStorage(): void
    {
        /** @var Payum $payum */
        $payum = static::getContainer()->get('payum');

        $this->assertInstanceOf(StorageInterface::class, $payum->getStorage(ArrayObject::class));
    }
}
