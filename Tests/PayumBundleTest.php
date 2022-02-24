<?php
namespace Payum\Bundle\PayumBundle\Tests;

use Payum\Bundle\PayumBundle\PayumBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PayumBundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBundle(): void
    {
        $rc = new \ReflectionClass(PayumBundle::class);

        $this->assertTrue($rc->isSubclassOf(Bundle::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments(): void
    {
        $this->expectNotToPerformAssertions();
        new PayumBundle;
    }
} 