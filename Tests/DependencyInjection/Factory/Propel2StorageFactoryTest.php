<?php

namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel2StorageFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class Propel2StorageFactoryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorageFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel2StorageFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Propel2StorageFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new Propel2StorageFactory;

        $this->assertEquals('propel2', $factory->getName());
    }

}
