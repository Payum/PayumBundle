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

        $reflected_constraint = (new \ReflectionObject($builder))->getProperty('coreGatewayFactory');
        $reflected_constraint->setAccessible(TRUE);
        $constraint = $reflected_constraint->getValue($builder);
        $this->assertInstanceOf(CoreGatewayFactoryBuilder::class, $constraint);
    }

    public function testShouldContainHttpRequestVerifierBuilder(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $reflected_constraint = (new \ReflectionObject($builder))->getProperty('httpRequestVerifier');
        $reflected_constraint->setAccessible(TRUE);
        $constraint = $reflected_constraint->getValue($builder);
        $this->assertInstanceOf(HttpRequestVerifierBuilder::class, $constraint);
    }

    public function testShouldContainTokenFactoryBuilder(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $reflected_constraint = (new \ReflectionObject($builder))->getProperty('tokenFactory');
        $reflected_constraint->setAccessible(TRUE);
        $constraint = $reflected_constraint->getValue($builder);
        $this->assertInstanceOf(TokenFactoryBuilder::class, $constraint);
    }

    public function testShouldContainMainRegistry(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $reflected_constraint = (new \ReflectionObject($builder))->getProperty('mainRegistry');
        $reflected_constraint->setAccessible(TRUE);
        $constraint = $reflected_constraint->getValue($builder);
        $this->assertInstanceOf(ContainerAwareRegistry::class, $constraint);
    }

    public function testShouldContainGenericTokenFactoryPaths(): void
    {
        /** @var PayumBuilder $builder */
        $builder = static::$container->get('payum.builder');

        $reflected_constraint = (new \ReflectionObject($builder))->getProperty('genericTokenFactoryPaths');
        $reflected_constraint->setAccessible(TRUE);
        $constraint = $reflected_constraint->getValue($builder);

        $this->assertEquals('payum_capture_do', $constraint['capture']);
        $this->assertEquals('payum_notify_do', $constraint['notify']);
        $this->assertEquals('payum_authorize_do', $constraint['authorize']);
        $this->assertEquals('payum_refund_do', $constraint['refund']);
        $this->assertEquals('payum_cancel_do', $constraint['cancel']);
        $this->assertEquals('payum_payout_do', $constraint['payout']);
    }
}
