<?php

namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildEntityManagerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildEntityManagerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementCompilerPassInterface()
    {
        $rc = new \ReflectionClass(BuildConfigsPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testShouldAddAliasToContainer()
    {
        $service = new Definition();

        $container = new ContainerBuilder();
        $container->setDefinition('doctrine.orm.custom_entity_manager', $service);
        $container->prependExtensionConfig('payum', [
            'entity_manager' => 'custom',
        ]);

        $pass = new BuildEntityManagerPass();

        $pass->process($container);

        $this->assertTrue($container->hasAlias('payum.entity_manager'));
    }

    public function testShouldNotAddAliasToContainer()
    {
        $container = new ContainerBuilder();
        $container->prependExtensionConfig('payum', [
            'entity_manager' => 'default',
        ]);

        $pass = new BuildEntityManagerPass();

        $pass->process($container);

        $this->assertFalse($container->hasAlias('payum.entity_manager'));
    }
}
