<?php
namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\NotifyController;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Notify;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class NotifyControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController()
    {
        $rc = new \ReflectionClass(NotifyController::class);

        $this->assertTrue($rc->isSubclassOf(Controller::class));
    }

    /**
     * @test
     */
    public function shouldExecuteNotifyRequestOnDoUnsafe()
    {
        $request = Request::create('/');
        $request->query->set('gateway', 'theGatewayName');

        $gatewayMock = $this->getMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Notify::class))
        ;

        $registryMock = $this->getMock(RegistryInterface::class);
        $registryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->will($this->returnValue($gatewayMock))
        ;

        $container = new Container;
        $container->set('payum', $registryMock);

        $controller = new NotifyController;
        $controller->setContainer($container);

        $response = $controller->doUnsafeAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }
}
