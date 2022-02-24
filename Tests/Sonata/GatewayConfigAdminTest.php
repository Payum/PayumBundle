<?php
namespace Payum\Bundle\PayumBundle\Tests\Sonata;

use Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\FormFactoryInterface;

class GatewayConfigAdminTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::markTestSkipped();
    }

    /**
     * @test
     */
    public function shouldBeSubClassSonataAdmin(): void
    {
        $rc = new \ReflectionClass(GatewayConfigAdmin::class);

        $this->assertTrue($rc->isSubclassOf(AbstractAdmin::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithExpectedArguments(): void
    {
        new GatewayConfigAdmin('code', 'class', 'baseControllerName');
    }

    /**
     * @test
     */
    public function shouldAllowSetFormFactory(): void
    {
        $admin = new GatewayConfigAdmin('code', 'class', 'baseControllerName');

        $formFactoryMock = $this->createMock(FormFactoryInterface::class);

        $admin->setFormFactory($formFactoryMock);

        $this->assertAttributeSame($formFactoryMock, 'formFactory', $admin);
    }
}

