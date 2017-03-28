<?php
namespace Payum\Bundle\PayumBundle\Sonata\Tests;

use Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\FormFactoryInterface;

class GatewayConfigAdminTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassSonataAdmin()
    {
        $rc = new \ReflectionClass(GatewayConfigAdmin::class);

        $this->assertTrue($rc->isSubclassOf(AbstractAdmin::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithExpectedArguments()
    {
        new GatewayConfigAdmin('code', 'class', 'baseControllerName');
    }

    /**
     * @test
     */
    public function shouldAllowSetFormFactory()
    {
        $admin = new GatewayConfigAdmin('code', 'class', 'baseControllerName');

        $formFactoryMock = $this->getMock(FormFactoryInterface::class);

        $admin->setFormFactory($formFactoryMock);

        $this->assertAttributeSame($formFactoryMock, 'formFactory', $admin);
    }
}

