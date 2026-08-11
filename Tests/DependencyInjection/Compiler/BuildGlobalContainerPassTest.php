<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGlobalContainerPass;
use Payum\Bundle\PayumBundle\DI\SymfonyContainerAdapter;
use Payum\Bundle\PayumBundle\PayumVersion;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use stdClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildGlobalContainerPassTest extends TestCase
{
    public function testShouldImplementCompilerPassInterface(): void
    {
        $rc = new ReflectionClass(BuildGlobalContainerPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testShouldDoNothingWithoutAGlobalContainer(): void
    {
        $container = new ContainerBuilder();

        (new BuildGlobalContainerPass())->process($container);

        $this->assertFalse($container->hasDefinition('payum.di.global_container'));
    }

    public function testShouldShareAServiceOfTheFramework(): void
    {
        $this->skipWithoutDependencyInjection();

        $container = $this->buildContainer();
        $container->setDefinition('logger', new Definition(NullLogger::class));

        (new BuildGlobalContainerPass())->process($container);

        $this->assertArrayHasKey(LoggerInterface::class, $this->providedServices($container));
    }

    public function testShouldNotShareAServiceOfTheFrameworkTheApplicationDoesNotHave(): void
    {
        $this->skipWithoutDependencyInjection();

        $container = $this->buildContainer();

        (new BuildGlobalContainerPass())->process($container);

        $this->assertArrayNotHasKey(LoggerInterface::class, $this->providedServices($container));
    }

    public function testShouldShareATaggedService(): void
    {
        $this->skipWithoutDependencyInjection();

        $container = $this->buildContainer();
        $container->setDefinition('acme.service', (new Definition(stdClass::class))->addTag('payum.global_service'));

        (new BuildGlobalContainerPass())->process($container);

        $this->assertArrayHasKey('acme.service', $this->providedServices($container));
    }

    public function testShouldShareATaggedServiceUnderTheGivenId(): void
    {
        $this->skipWithoutDependencyInjection();

        $container = $this->buildContainer();
        $container->setDefinition('acme.service', (new Definition(stdClass::class))->addTag('payum.global_service', [
            'id' => 'Acme\\ServiceInterface',
        ]));

        (new BuildGlobalContainerPass())->process($container);

        $services = $this->providedServices($container);

        $this->assertArrayHasKey('Acme\\ServiceInterface', $services);
        $this->assertArrayNotHasKey('acme.service', $services);
    }

    private function skipWithoutDependencyInjection(): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('The global container needs payum/core 2.0 or later.');
        }
    }

    private function buildContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition('payum.di.global_container', new Definition(SymfonyContainerAdapter::class, [null]));

        return $container;
    }

    /**
     * @return array<string, mixed>
     */
    private function providedServices(ContainerBuilder $container): array
    {
        $locator = $container->getDefinition('payum.di.global_container')->getArgument(0);

        return $container->getDefinition((string) $locator)->getArgument(0);
    }
}
