<?php

namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\NotifyController;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyControllerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController()
    {
        $rc = new \ReflectionClass(NotifyController::class);

        $this->assertTrue($rc->isSubclassOf(AbstractController::class));
    }

    /**
     * @test
     */
    public function shouldExecuteNotifyRequestOnDoUnsafe()
    {
        $request = Request::create('/');
        $request->query->set('gateway', 'theGatewayName');

        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Notify::class));

        $registryMock = $this->createMock(RegistryInterface::class);
        $registryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->will($this->returnValue($gatewayMock));

        $controller = new NotifyController($registryMock);

        $response = $controller->doUnsafeAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }
}
