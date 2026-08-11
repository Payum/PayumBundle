<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests;

use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Core\DI\ContainerConfiguration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PayumVersionTest extends TestCase
{
    public function testShouldBeFinal(): void
    {
        $rc = new ReflectionClass(PayumVersion::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testShouldTellWhetherPayumBuildsItsGatewaysWithAContainer(): void
    {
        $this->assertSame(
            interface_exists(ContainerConfiguration::class),
            PayumVersion::supportsDependencyInjection()
        );
    }

    public function testShouldGiveTheSameAnswerEveryTime(): void
    {
        $this->assertSame(
            PayumVersion::supportsDependencyInjection(),
            PayumVersion::supportsDependencyInjection()
        );
    }
}
