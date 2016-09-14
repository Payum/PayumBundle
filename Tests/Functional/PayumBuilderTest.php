<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Core\Bridge\Symfony\Builder\CoreGatewayFactoryBuilder;
use Payum\Core\Bridge\Symfony\Builder\HttpRequestVerifierBuilder;
use Payum\Core\Bridge\Symfony\Builder\TokenFactoryBuilder;
use Payum\Core\Bridge\Symfony\ContainerAwareRegistry;
use Payum\Core\PayumBuilder;

class PayumBuilderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        /** @var PayumBuilder $builder */
        $builder = $this->container->get('payum.builder');

        $this->assertInstanceOf(PayumBuilder::class, $builder);
    }

    public function testShouldContainCoreGatewayFactoryBuilder()
    {
        /** @var PayumBuilder $builder */
        $builder = $this->container->get('payum.builder');

        $this->assertAttributeInstanceOf(CoreGatewayFactoryBuilder::class, 'coreGatewayFactory', $builder);
    }

    public function testShouldContainHttpRequestVerifierBuilder()
    {
        /** @var PayumBuilder $builder */
        $builder = $this->container->get('payum.builder');

        $this->assertAttributeInstanceOf(HttpRequestVerifierBuilder::class, 'httpRequestVerifier', $builder);
    }

    public function testShouldContainTokenFactoryBuilder()
    {
        /** @var PayumBuilder $builder */
        $builder = $this->container->get('payum.builder');

        $this->assertAttributeInstanceOf(TokenFactoryBuilder::class, 'tokenFactory', $builder);
    }

    public function testShouldContainMainRegistry()
    {
        /** @var PayumBuilder $builder */
        $builder = $this->container->get('payum.builder');

        $this->assertAttributeInstanceOf(ContainerAwareRegistry::class, 'mainRegistry', $builder);
    }

    public function testShouldContainGenericTokenFactoryPaths()
    {
        /** @var PayumBuilder $builder */
        $builder = $this->container->get('payum.builder');

        $this->assertAttributeEquals([
            'capture' => 'payum_capture_do',
            'notify' => 'payum_notify_do',
            'authorize' => 'payum_authorize_do',
            'refund' => 'payum_refund_do',
            'cancel' => 'payum_cancel_do',
            'payout' => 'payum_payout_do',
        ], 'genericTokenFactoryPaths', $builder);
    }
}