<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Storage\StorageInterface;
use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;
use Payum\Stripe\StripeJsGatewayFactory;

class PayumTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $this->assertInstanceOf(Payum::class, $payum);
    }

    public function testShouldReturnHttpRequestVerifyRequest()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $this->assertInstanceOf(HttpRequestVerifier::class, $payum->getHttpRequestVerifier());
    }

    public function testShouldReturnTokenFactory()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $tokenFactory = $payum->getTokenFactory();
        $this->assertInstanceOf(GenericTokenFactory::class, $tokenFactory);
        $this->assertAttributeInstanceOf(TokenFactory::class, 'tokenFactory', $tokenFactory);
    }

    public function testShouldReturnTokenStorage()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $storage = $payum->getTokenStorage();
        $this->assertInstanceOf(StorageInterface::class, $storage);
    }

    public function testShouldReturnStorages()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $storages = $payum->getStorages();
        $this->assertInternalType('array', $storages);
        $this->assertCount(1, $storages);
    }

    public function testShouldReturnGateways()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $gateways = $payum->getGateways();
        $this->assertInternalType('array', $gateways);
        $this->assertCount(2, $gateways);
    }

    public function testShouldReturnGatewaysFactories()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $factories = $payum->getGatewayFactories();
        $this->assertInternalType('array', $factories);
        $this->assertGreaterThan(10, count($factories));
    }

    public function testShouldReturnGatewayFactory()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $this->assertInstanceOf(PaypalExpressCheckoutGatewayFactory::class, $payum->getGatewayFactory('paypal_express_checkout'));
        $this->assertInstanceOf(StripeJsGatewayFactory::class, $payum->getGatewayFactory('stripe_js'));
    }

    public function testShouldReturnGateway()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $this->assertInstanceOf(GatewayInterface::class, $payum->getGateway('fooGateway'));
        $this->assertInstanceOf(GatewayInterface::class, $payum->getGateway('barGateway'));
    }

    public function testShouldReturnStorage()
    {
        /** @var Payum $payum */
        $payum = $this->container->get('payum');

        $this->assertInstanceOf(StorageInterface::class, $payum->getStorage(ArrayObject::class));
    }
}