<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AuthorizeNetAimGatewayFactory;
use Symfony\Component\HttpKernel\Kernel;

class AuthorizeNetAimGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AuthorizeNetAimGatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AuthorizeNetAimGatewayFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new AuthorizeNetAimGatewayFactory;

        $this->assertEquals('authorize_net_aim', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new AuthorizeNetAimGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
        )));

        $this->assertArrayHasKey('login_id', $config);
        $this->assertEquals('aLoginId', $config['login_id']);
        
        $this->assertArrayHasKey('transaction_key', $config);
        $this->assertEquals('aTransactionKey', $config['transaction_key']);

        $this->assertArrayHasKey('sandbox', $config);
        $this->assertTrue($config['sandbox']);

        //come from abstract gateway factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "login_id" at path "foo" must be configured.
     */
    public function thrownIfLoginIdOptionNotSet()
    {
        $factory = new AuthorizeNetAimGatewayFactory;

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
     * @expectedExceptionMessage The child node "transaction_key" at path "foo" must be configured.
     */
    public function thrownIfTransactionKeyOptionNotSet()
    {
        $factory = new AuthorizeNetAimGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'login_id' => 'aLoginId'
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayAndReturnItsId()
    {
        $factory = new AuthorizeNetAimGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.authorize_net_aim.aGatewayName.gateway', $gatewayId);
        $this->assertTrue($container->hasDefinition($gatewayId));

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
    public function shouldLoadFactory()
    {
        $factory = new AuthorizeNetAimGatewayFactory;

        $container = new ContainerBuilder;
        $container->setDefinition('payum.builder', new Definition());

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.authorize_net_aim.factory'));

        $factoryService = $container->getDefinition('payum.authorize_net_aim.factory');
        $this->assertEquals('Payum\AuthorizeNet\Aim\AuthorizeNetAimGatewayFactory', $factoryService->getClass());

        $this->assertNotEmpty($factoryService->getFactory());
        $this->assertEquals('payum', (string) $factoryService->getFactory()[0]);
        $this->assertEquals('getGatewayFactory', $factoryService->getFactory()[1]);
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new AuthorizeNetAimGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
            'sandbox' => true,
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