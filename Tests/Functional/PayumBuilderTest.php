<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Core\Bridge\Symfony\Builder\CoreGatewayFactoryBuilder;
use Payum\Core\Bridge\Symfony\Builder\HttpRequestVerifierBuilder;
use Payum\Core\Bridge\Symfony\Builder\TokenFactoryBuilder;
use Payum\Core\Bridge\Symfony\ContainerAwareRegistry;
use Payum\Core\PayumBuilder;

class PayumBuilderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $this->assertInstanceOf(PayumBuilder::class, $builder);
    }


    public function testShouldContainCoreGatewayFactoryBuilder(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $this->markTestIncomplete(
            'Replacement for PhpUnit9 needed. Attribut is not public in Payum\Core\PayumBuilder'
        );
        #$this->assertAttributeInstanceOf(CoreGatewayFactoryBuilder::class, 'coreGatewayFactory', $builder);
        #$this->assertInstanceOf(CoreGatewayFactoryBuilder::class, $builder->coreGatewayFactory);
    }

    public function testShouldContainHttpRequestVerifierBuilder(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $this->markTestIncomplete(
            'Replacement for PhpUnit9 needed. Attribut is not public in Payum\Core\PayumBuilder'
        );
        #$this->assertAttributeInstanceOf(HttpRequestVerifierBuilder::class, 'httpRequestVerifier', $builder);
        #$this->assertInstanceOf(HttpRequestVerifierBuilder::class, $builder->httpRequestVerifier);
    }

    public function testShouldContainTokenFactoryBuilder(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $this->markTestIncomplete(
            'Replacement for PhpUnit9 needed. Attribut is not public in Payum\Core\PayumBuilder'
        );
        #$this->assertAttributeInstanceOf(TokenFactoryBuilder::class, 'tokenFactory', $builder);
        #$this->assertInstanceOf(TokenFactoryBuilder::class, $builder->tokenFactory);
    }

    public function testShouldContainMainRegistry(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $this->markTestIncomplete(
            'Replacement for PhpUnit9 needed. Attribut is not public in Payum\Core\Bridge\Symfony\ContainerAwareRegistry'
        );
        #$this->assertAttributeInstanceOf(ContainerAwareRegistry::class, 'mainRegistry', $builder);
        #$this->assertInstanceOf(ContainerAwareRegistry::class, $builder->mainRegistry);
    }

    public function testShouldContainGenericTokenFactoryPaths(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $this->markTestIncomplete(
            'Replacement for PhpUnit9 needed.'
        );

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
