<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildMiddlewaresPass;
use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Core\PayumBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BuildMiddlewaresPassTest extends TestCase
{
    public function testShouldAddTaggedMiddlewareToTheBuilderWithItsPriority(): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('Middleware needs payum/core 2.0 or later.');
        }

        $container = new ContainerBuilder();
        $container->setDefinition('payum.builder', new Definition(PayumBuilder::class));
        $container->setDefinition('acme.outer', (new Definition(stdClass::class))->addTag('payum.middleware', ['priority' => 1500]));
        $container->setDefinition('acme.default', (new Definition(stdClass::class))->addTag('payum.middleware'));

        (new BuildMiddlewaresPass())->process($container);

        $this->assertEquals([
            ['addMiddleware', [new Reference('acme.outer'), 1500]],
            ['addMiddleware', [new Reference('acme.default'), null]],
        ], $container->getDefinition('payum.builder')->getMethodCalls());
    }

    public function testShouldAddAMiddlewareTaggedTwiceOnceWithThePriorityOneTagGives(): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('Middleware needs payum/core 2.0 or later.');
        }

        // what autoconfiguration does to a MiddlewareInterface class with #[AsPayumMiddleware(priority: 1500)]
        $container = new ContainerBuilder();
        $container->setDefinition('payum.builder', new Definition(PayumBuilder::class));
        $container->setDefinition('acme.middleware', (new Definition(stdClass::class))
            ->addTag('payum.middleware')
            ->addTag('payum.middleware', ['priority' => 1500]));

        (new BuildMiddlewaresPass())->process($container);

        $this->assertEquals([
            ['addMiddleware', [new Reference('acme.middleware'), 1500]],
        ], $container->getDefinition('payum.builder')->getMethodCalls());
    }
}
