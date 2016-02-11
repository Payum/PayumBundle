<?php
namespace Payum\Bundle\PayumBundle\Tests;

use Payum\Bundle\PayumBundle\PayumBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PayumBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBundle()
    {
        $rc = new \ReflectionClass(PayumBundle::class);

        $this->assertTrue($rc->isSubclassOf(Bundle::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayumBundle;
    }
} 