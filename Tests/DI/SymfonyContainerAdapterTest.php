<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\DI;

use Payum\Bundle\PayumBundle\DI\SymfonyContainerAdapter;
use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Core\DI\ListableContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;

class SymfonyContainerAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('The global container needs payum/core 2.0 or later.');
        }
    }

    public function testShouldImplementContainerInterface(): void
    {
        $rc = new ReflectionClass(SymfonyContainerAdapter::class);

        $this->assertTrue($rc->implementsInterface(ContainerInterface::class));
    }

    public function testShouldImplementListableContainerInterface(): void
    {
        if (! interface_exists(ListableContainerInterface::class)) {
            $this->markTestSkipped('The installed payum/core cannot be told a container lists its entries.');
        }

        $rc = new ReflectionClass(SymfonyContainerAdapter::class);

        $this->assertTrue($rc->implementsInterface(ListableContainerInterface::class));
    }

    public function testShouldResolveAServiceOfTheLocator(): void
    {
        $service = new stdClass();

        $container = new SymfonyContainerAdapter(new ServiceLocator([
            'a_service' => static fn (): stdClass => $service,
        ]));

        $this->assertTrue($container->has('a_service'));
        $this->assertSame($service, $container->get('a_service'));
    }

    public function testShouldNotHaveAServiceTheLocatorDoesNotProvide(): void
    {
        $container = new SymfonyContainerAdapter(new ServiceLocator([]));

        $this->assertFalse($container->has('a_service'));
    }

    public function testShouldReportTheEntriesOfTheLocator(): void
    {
        $container = new SymfonyContainerAdapter(new ServiceLocator([
            'a_service' => static fn (): stdClass => new stdClass(),
            'another_service' => static fn (): stdClass => new stdClass(),
        ]));

        $this->assertSame(['a_service', 'another_service'], $container->getKnownEntryNames());
    }

    public function testShouldReportNoEntryForAnEmptyLocator(): void
    {
        $container = new SymfonyContainerAdapter(new ServiceLocator([]));

        $this->assertSame([], $container->getKnownEntryNames());
    }
}
