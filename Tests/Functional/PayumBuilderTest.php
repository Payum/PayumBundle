<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Bundle\PayumBundle\Builder\CoreGatewayFactoryBuilder;
use Payum\Bundle\PayumBundle\Builder\HttpRequestVerifierBuilder;
use Payum\Bundle\PayumBundle\Builder\TokenFactoryBuilder;
use Payum\Bundle\PayumBundle\ContainerAwareRegistry;
use Payum\Core\PayumBuilder;

class PayumBuilderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::getContainer()->get('payum.builder');

        $this->assertInstanceOf(PayumBuilder::class, $builder);
    }


    public function testShouldContainCoreGatewayFactoryBuilder(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::getContainer()->get('payum.builder');

        $reflectedConstraint = (new \ReflectionObject($builder))->getProperty('coreGatewayFactory');
        $reflectedConstraint->setAccessible(true);
        $constraint = $reflectedConstraint->getValue($builder);
        $this->assertInstanceOf(CoreGatewayFactoryBuilder::class, $constraint);
    }

    public function testShouldContainHttpRequestVerifierBuilder(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::getContainer()->get('payum.builder');

        $reflectedConstraint = (new \ReflectionObject($builder))->getProperty('httpRequestVerifier');
        $reflectedConstraint->setAccessible(true);
        $constraint = $reflectedConstraint->getValue($builder);
        $this->assertInstanceOf(HttpRequestVerifierBuilder::class, $constraint);
    }

    public function testShouldContainTokenFactoryBuilder(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::getContainer()->get('payum.builder');

        $reflectedConstraint = (new \ReflectionObject($builder))->getProperty('tokenFactory');
        $reflectedConstraint->setAccessible(true);
        $constraint = $reflectedConstraint->getValue($builder);
        $this->assertInstanceOf(TokenFactoryBuilder::class, $constraint);
    }

    public function testShouldContainMainRegistry(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::getContainer()->get('payum.builder');

        $reflectedConstraint = (new \ReflectionObject($builder))->getProperty('mainRegistry');
        $reflectedConstraint->setAccessible(true);
        $constraint = $reflectedConstraint->getValue($builder);
        $this->assertInstanceOf(ContainerAwareRegistry::class, $constraint);
    }

    public function testShouldContainGenericTokenFactoryPaths(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::getContainer()->get('payum.builder');

        $reflectedConstraint = (new \ReflectionObject($builder))->getProperty('genericTokenFactoryPaths');
        $reflectedConstraint->setAccessible(true);
        $constraint = $reflectedConstraint->getValue($builder);

        $this->assertEquals('payum_capture_do', $constraint['capture']);
        $this->assertEquals('payum_notify_do', $constraint['notify']);
        $this->assertEquals('payum_authorize_do', $constraint['authorize']);
        $this->assertEquals('payum_refund_do', $constraint['refund']);
        $this->assertEquals('payum_cancel_do', $constraint['cancel']);
        $this->assertEquals('payum_payout_do', $constraint['payout']);
    }
}
