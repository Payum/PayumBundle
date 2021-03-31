<?php

namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\NotifyController;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ServiceLocator;
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
            ->expects($this->any())
            ->method('execute')
            ->with($this->isInstanceOf(Notify::class));

        $registryMock = $this->createMock(Payum::class);
        $registryMock
            ->expects($this->any())
            ->method('getGateway')
            ->with('theGatewayName')
            ->will($this->returnValue($gatewayMock));

        $controller = new NotifyController();
        $controller->setContainer(new ServiceLocator(['payum' => function () use ($registryMock) { return $registryMock; }]));

        $response = $controller->doUnsafeAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }
}
