<?php

namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\NotifyController;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
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
    public function shouldBeSubClassOfController(): void
    {
        $rc = new \ReflectionClass(NotifyController::class);

        $this->assertTrue($rc->isSubclassOf(AbstractController::class));
    }

    /**
     * @test
     */
    public function shouldExecuteNotifyRequestOnDoUnsafe(): void
    {
        $request = Request::create('/');
        $request->attributes->set('gateway', 'theGatewayName');

        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->method('execute')
            ->with($this->isInstanceOf(Notify::class));

        $payumMock = $this->createMock(Payum::class);
        $payumMock
            ->method('getGateway')
            ->with('theGatewayName')
            ->willReturn($gatewayMock);

        $controller = new NotifyController($payumMock);

        $response = $controller->doUnsafeAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }
}
