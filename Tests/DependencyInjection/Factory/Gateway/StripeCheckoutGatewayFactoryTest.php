<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Gateway;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeCheckoutGatewayFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

class StripeCheckoutGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeCheckoutGatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory'));
    }

    /**
     * @test
     */
    public function shouldImplementPrependExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeCheckoutGatewayFactory');

        $this->assertTrue($rc->implementsInterface(PrependExtensionInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new StripeCheckoutGatewayFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $this->assertEquals('stripe_checkout', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'publishable_key' => 'thePubKey',
            'secret_key' => 'theSecretKey',
        )));

        $this->assertArrayHasKey('publishable_key', $config);
        $this->assertEquals('thePubKey', $config['publishable_key']);
        
        $this->assertArrayHasKey('secret_key', $config);
        $this->assertEquals('theSecretKey', $config['secret_key']);

        //come from abstract gateway factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "publishable_key" at path "foo" must be configured.
     */
    public function thrownIfPublishableKeyOptionMissed()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "secret_key" at path "foo" must be configured.
     */
    public function thrownIfSecretKeyOptionMissed()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'publishable_key' => 'aPubKey',
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayAndReturnItsId()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.stripe_checkout.aGatewayName.gateway', $gatewayId);
        $this->assertTrue($container->hasDefinition($gatewayId));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithExpectedConfig()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.stripe_checkout.aGatewayName.gateway', $gatewayId);

        $gateway = $container->getDefinition($gatewayId);

        //guard
        $this->assertNotEmpty($gateway->getFactory());
        $this->assertNotEmpty($gateway->getArguments());

        $config = $gateway->getArgument(0);

        $this->assertEquals('aGatewayName', $config['payum.gateway_name']);
    }

    /**
     * @test
     */
    public function shouldLoadFactoryAndTemplates()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $container = new ContainerBuilder;
        $container->setDefinition('payum.builder', new Definition());

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.stripe_checkout.factory'));

        $factoryService = $container->getDefinition('payum.stripe_checkout.factory');
        $this->assertEquals('Payum\Stripe\StripeCheckoutGatewayFactory', $factoryService->getClass());

        $this->assertNotEmpty($factoryService->getFactory());
        $this->assertEquals('payum', (string) $factoryService->getFactory()[0]);
        $this->assertEquals('getGatewayFactory', $factoryService->getFactory()[1]);
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'actions' => array('payum.action.foo'),
            'apis' => array('payum.api.bar'),
            'extensions' => array('payum.extension.ololo'),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addAction', 
            new Reference('payum.action.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addApi',
            new Reference('payum.api.bar')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addExtension',
            new Reference('payum.extension.ololo')
        );
    }

    /**
     * @test
     */
    public function shouldPrependTwigsExtensionConfig()
    {
        $factory = new StripeCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $factory->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');

        //guard
        $this->assertTrue(isset($twigConfig[0]['paths']));

        $paths = $twigConfig[0]['paths'];

        $key = array_search('PayumCore', $paths);
        $this->assertFileExists($key);

        $key = array_search('PayumStripe', $paths);
        $this->assertFileExists($key);
    }

    protected function assertDefinitionContainsMethodCall(Definition $serviceDefinition, $expectedMethod, $expectedFirstArgument)
    {
        foreach ($serviceDefinition->getMethodCalls() as $methodCall) {
            if ($expectedMethod == $methodCall[0] && $expectedFirstArgument == $methodCall[1][0]) {
                return;
            }
        }

        $this->fail(sprintf(
            'Failed assert that service (Class: %s) has method %s been called with first argument %s',
            $serviceDefinition->getClass(),
            $expectedMethod,
            $expectedFirstArgument
        ));
    }
}