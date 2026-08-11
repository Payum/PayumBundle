<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\PayumCoreGatewayFactory;
use Payum\Bundle\PayumBundle\PayumVersion;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BuildConfigsPassTest extends \PHPUnit\Framework\TestCase
{
    public function provideTags(): array
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

    public function testShouldImplementCompilerPassInterface(): void
    {
        $rc = new \ReflectionClass(BuildConfigsPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    /**
     * The tagged services of payum/core 2.0 and later, as entries of the gateway containers.
     */
    public function provideContainerTags(): array
    {
        $entry = static fn (bool $prepend = false): array => [[
            'service' => new Reference('aservice'),
            'prepend' => $prepend,
        ]];

        $keys = [
            'payum.action' => [PayumCoreGatewayFactory::SHARED_ACTIONS, PayumCoreGatewayFactory::ACTIONS],
            'payum.api' => [PayumCoreGatewayFactory::SHARED_APIS, PayumCoreGatewayFactory::APIS],
            'payum.extension' => [PayumCoreGatewayFactory::SHARED_EXTENSIONS, PayumCoreGatewayFactory::EXTENSIONS],
        ];

        $sets = [];

        foreach ($keys as $tag => [$sharedKey, $scopedKey]) {
            $sets[$tag . ' untagged scope'] = [['name' => $tag], []];
            $sets[$tag . ' all'] = [['name' => $tag, 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [[$sharedKey => $entry()]],
            ]]];
            // an alias no longer names anything: a service is an entry of a list, not a config key
            $sets[$tag . ' all with alias'] = [['name' => $tag, 'alias' => 'foo', 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [[$sharedKey => $entry()]],
            ]]];
            $sets[$tag . ' all prepended'] = [['name' => $tag, 'prepend' => true, 'all' => true], [[
                'addCoreGatewayFactoryConfig',
                [[$sharedKey => $entry(true)]],
            ]]];
            $sets[$tag . ' for a factory'] = [['name' => $tag, 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                ['fooFactory', [$scopedKey => $entry()]],
            ]]];
            $sets[$tag . ' for a factory prepended'] = [['name' => $tag, 'prepend' => true, 'factory' => 'fooFactory'], [[
                'addGatewayFactoryConfig',
                ['fooFactory', [$scopedKey => $entry(true)]],
            ]]];
            $sets[$tag . ' for a gateway'] = [['name' => $tag, 'gateway' => 'fooGateway'], [[
                'addGateway',
                ['fooGateway', [$scopedKey => $entry()]],
            ]]];
            $sets[$tag . ' for a gateway prepended'] = [['name' => $tag, 'prepend' => true, 'gateway' => 'fooGateway'], [[
                'addGateway',
                ['fooGateway', [$scopedKey => $entry(true)]],
            ]]];
        }

        return $sets;
    }

    /**
     * @dataProvider provideTags
     */
    public function testShouldAddConfig(array $tagAttributes, $expected): void
    {
        if (PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('The configuration of payum/core 1.x only.');
        }

        $this->assertSame($expected, $this->processTag($tagAttributes));
    }

    /**
     * @dataProvider provideContainerTags
     */
    public function testShouldAddContainerEntries(array $tagAttributes, $expected): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('The configuration of payum/core 2.0 and later only.');
        }

        $this->assertEquals($expected, $this->processTag($tagAttributes));
    }

    private function processTag(array $tagAttributes): array
    {
        $tagName = $tagAttributes['name'];
        unset($tagAttributes['name']);

        $service = new Definition();
        $service->addTag($tagName, $tagAttributes);

        $builder = new Definition();

        $container = new ContainerBuilder();
        $container->setDefinition('payum.builder', $builder);
        $container->setDefinition('aservice', $service);

        (new BuildConfigsPass())->process($container);

        return $builder->getMethodCalls();
    }
}
