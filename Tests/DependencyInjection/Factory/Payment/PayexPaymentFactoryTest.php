<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PayexPaymentFactory;

class PayexPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() 
    {
        if (false == class_exists('Payum\Payex\PaymentFactory')) {
            throw new \PHPUnit_Framework_SkippedTestError('Skipped because payment library is not installed.');
        }
    }
    
    public static function provideDecoratedActions()
    {
        return array(
            'api.initialize_order' => array('payum.context.aContextName.action.api.initialize_order'),
            'api.complete_order' => array('payum.context.aContextName.action.api.complete_order'),
            'api.check_order' => array('payum.context.aContextName.action.api.check_order'),
            'api.create_agreement' => array('payum.context.aContextName.action.api.create_agreement'),
            'api.delete_agreement' => array('payum.context.aContextName.action.api.delete_agreement'),
            'api.check_agreement' => array('payum.context.aContextName.action.api.check_agreement'),
            'api.autopay_agreement' => array('payum.context.aContextName.action.api.autopay_agreement'),
            'api.start_recurring_payment' => array('payum.context.aContextName.action.api.start_recurring_payment'),
            'api.stop_recurring_payment' => array('payum.context.aContextName.action.api.stop_recurring_payment'),
            'api.check_recurring_payment' => array('payum.context.aContextName.action.api.check_recurring_payment'),

            'payment_details_capture' => array('payum.context.aContextName.action.payment_details_capture'),
            'payment_details_status' => array('payum.context.aContextName.action.payment_details_status'),
            'payment_details_sync' => array('payum.context.aContextName.action.payment_details_sync'),
            'autopay_payment_details_capture' => array('payum.context.aContextName.action.autopay_payment_details_capture'),
            'autopay_payment_details_status' => array('payum.context.aContextName.action.autopay_payment_details_status'),
            'agreement_details_status' => array('payum.context.aContextName.action.agreement_details_status'),
            'agreement_details_sync' => array('payum.context.aContextName.action.agreement_details_sync'),
        );
    }
    
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PayexPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayexPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new PayexPaymentFactory;

        $this->assertEquals('payex', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'api' => array( 
                'options' => array(
                    'encryption_key' => 'aKey',
                    'account_number' => 'aNum',
                )
            )
        )));
        
        $this->assertArrayHasKey('api', $config);
        
        $this->assertArrayHasKey('options', $config['api']);
        
        $this->assertArrayHasKey('encryption_key', $config['api']['options']);
        $this->assertEquals('aKey', $config['api']['options']['encryption_key']);

        $this->assertArrayHasKey('account_number', $config['api']['options']);
        $this->assertEquals('aNum', $config['api']['options']['account_number']);

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "api" at path "foo" must be configured.
     */
    public function thrownIfApiSectionMissing()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array());
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "options" at path "foo.api" must be configured.
     */
    public function thrownIfApiOptionsSectionMissing()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array()
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "encryption_key" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionEncryptionKeySectionMissing()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array(
                'options' => array()
            )
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "account_number" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionAccountNumberSectionMissing()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array(
                'options' => array(
                    'encryption_key' => 'aKey'
                )
            )
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'options' => array(
                    'encryption_key' => 'aKey',
                    'account_number' => 'aNum',
                    'sandbox' => true
                ),
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.context.aContextName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'options' => array(
                    'encryption_key' => 'aKey',
                    'account_number' => 'aNum',
                    'sandbox' => true
                ),
            ),
            'actions' => array('payum.action.foo'),
            'apis' => array('payum.api.bar'),
            'extensions' => array('payum.extension.ololo'),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId), 
            'addAction', 
            new Reference('payum.action.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.api.bar')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addExtension',
            new Reference('payum.extension.ololo')
        );
    }

    /**
     * @test
     */
    public function shouldAddExpectedApisToPayment()
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'options' => array(
                    'encryption_key' => 'aKey',
                    'account_number' => 'aNum',
                    'sandbox' => true
                ),
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.api.order')
        );

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.api.agreement')
        );

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.api.recurring')
        );
    }

    /**
     * @test
     * 
     * @dataProvider provideDecoratedActions
     */
    public function shouldDecorateExpectedActionDefinitionsAndAddItToPayment($expectedActionDefinitionId)
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'options' => array(
                    'encryption_key' => 'aKey',
                    'account_number' => 'aNum',
                    'sandbox' => true
                ),
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addAction',
            new Reference($expectedActionDefinitionId)
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