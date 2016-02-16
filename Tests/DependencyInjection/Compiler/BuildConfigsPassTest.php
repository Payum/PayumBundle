<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildConfigsPassTest extends \PHPUnit_Framework_TestCase
{
    public function provideTags()
    {
        return [
            0 => [['name' => 'payum.action'], []],
            1 => [['name' => 'payum.action', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.action.aservice' => '@aservice']]
            ]]],
            2 => [['name' => 'payum.action', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.action.aservice' => '@aservice']]
            ]]],
            3 => [['name' => 'payum.action', 'alias' => 'foo', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.action.foo' => '@aservice']]
            ]]],
            4 => [['name' => 'payum.action', 'prepend' => true, 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [[
                    'payum.action.aservice' => '@aservice',
                    'payum.prepend_actions' => ['payum.action.aservice']
                ]]
            ]]],
            5 => [['name' => 'payum.action', 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                ['fooFactory', ['payum.action.aservice' => '@aservice']]
            ]]],
            6 => [['name' => 'payum.action', 'alias' => 'foo', 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                ['fooFactory', ['payum.action.foo' => '@aservice']]
            ]]],
            7 => [['name' => 'payum.action', 'prepend' => true, 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                [
                    'fooFactory',
                    [
                        'payum.action.aservice' => '@aservice',
                        'payum.prepend_actions' => ['payum.action.aservice']
                    ]
                ]
            ]]],
            8 => [['name' => 'payum.action', 'gateway' => 'fooGateway'], [[
                'addGateway',
                ['fooGateway', ['payum.action.aservice' => '@aservice']]
            ]]],
            9 => [['name' => 'payum.action', 'alias' => 'foo', 'gateway' => 'fooGateway'], [[
                'addGateway',
                ['fooGateway', ['payum.action.foo' => '@aservice']]
            ]]],
            10 => [['name' => 'payum.action', 'prepend' => true, 'gateway' => 'fooGateway'], [[
                'addGateway',
                [
                    'fooGateway',
                    [
                        'payum.action.aservice' => '@aservice',
                        'payum.prepend_actions' => ['payum.action.aservice']
                    ]
                ]
            ]]],
            11 => [['name' => 'payum.api'], []],
            12 => [['name' => 'payum.api', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.api.aservice' => '@aservice']]
            ]]],
            13 => [['name' => 'payum.api', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.api.aservice' => '@aservice']]
            ]]],
            14 => [['name' => 'payum.api', 'alias' => 'foo', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.api.foo' => '@aservice']]
            ]]],
            15 => [['name' => 'payum.api', 'prepend' => true, 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [[
                    'payum.api.aservice' => '@aservice',
                    'payum.prepend_apis' => ['payum.api.aservice']
                ]]
            ]]],
            16 => [['name' => 'payum.api', 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                ['fooFactory', ['payum.api.aservice' => '@aservice']]
            ]]],
            17 => [['name' => 'payum.api', 'alias' => 'foo', 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                ['fooFactory', ['payum.api.foo' => '@aservice']]
            ]]],
            18 => [['name' => 'payum.api', 'prepend' => true, 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                [
                    'fooFactory',
                    [
                        'payum.api.aservice' => '@aservice',
                        'payum.prepend_apis' => ['payum.api.aservice']
                    ]
                ]
            ]]],
            19 => [['name' => 'payum.api', 'gateway' => 'fooGateway'], [[
                'addGateway',
                ['fooGateway', ['payum.api.aservice' => '@aservice']]
            ]]],
            20 => [['name' => 'payum.api', 'alias' => 'foo', 'gateway' => 'fooGateway'], [[
                'addGateway',
                ['fooGateway', ['payum.api.foo' => '@aservice']]
            ]]],
            21 => [['name' => 'payum.api', 'prepend' => true, 'gateway' => 'fooGateway'], [[
                'addGateway',
                [
                    'fooGateway',
                    [
                        'payum.api.aservice' => '@aservice',
                        'payum.prepend_apis' => ['payum.api.aservice']
                    ]
                ]
            ]]],
            22 => [['name' => 'payum.extension'], []],
            23 => [['name' => 'payum.extension', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.extension.aservice' => '@aservice']]
            ]]],
            24 => [['name' => 'payum.extension', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.extension.aservice' => '@aservice']]
            ]]],
            25 => [['name' => 'payum.extension', 'alias' => 'foo', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [['payum.extension.foo' => '@aservice']]
            ]]],
            26 => [['name' => 'payum.extension', 'prepend' => true, 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [[
                    'payum.extension.aservice' => '@aservice',
                    'payum.prepend_extensions' => ['payum.extension.aservice']
                ]]
            ]]],
            27 => [['name' => 'payum.extension', 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                ['fooFactory', ['payum.extension.aservice' => '@aservice']]
            ]]],
            28 => [['name' => 'payum.extension', 'alias' => 'foo', 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                ['fooFactory', ['payum.extension.foo' => '@aservice']]
            ]]],
            29 => [['name' => 'payum.extension', 'prepend' => true, 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                [
                    'fooFactory',
                    [
                        'payum.extension.aservice' => '@aservice',
                        'payum.prepend_extensions' => ['payum.extension.aservice']
                    ]
                ]
            ]]],
            30 => [['name' => 'payum.extension', 'gateway' => 'fooGateway'], [[
                'addGateway',
                ['fooGateway', ['payum.extension.aservice' => '@aservice']]
            ]]],
            31 => [['name' => 'payum.extension', 'alias' => 'foo', 'gateway' => 'fooGateway'], [[
                'addGateway',
                ['fooGateway', ['payum.extension.foo' => '@aservice']]
            ]]],
            32 => [['name' => 'payum.extension', 'prepend' => true, 'gateway' => 'fooGateway'], [[
                'addGateway',
                [
                    'fooGateway',
                    [
                        'payum.extension.aservice' => '@aservice',
                        'payum.prepend_extensions' => ['payum.extension.aservice']
                    ]
                ]
            ]]],
        ];
    }

    public function testShouldImplementCompilerPassInterface()
    {
        $rc = new \ReflectionClass(BuildConfigsPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    /**
     * @dataProvider provideTags
     */
    public function testShouldAddConfig(array $tagAttributes, $expected)
    {
        $tagName = $tagAttributes['name'];
        unset($tagAttributes['name']);

        $service = new Definition();
        $service->addTag($tagName, $tagAttributes);

        $builder = new Definition();

        $container = new ContainerBuilder();
        $container->setDefinition('payum.builder', $builder);
        $container->setDefinition('aService', $service);

        $pass = new BuildConfigsPass();

        $pass->process($container);

        $this->assertEquals($expected, $builder->getMethodCalls());
    }
}