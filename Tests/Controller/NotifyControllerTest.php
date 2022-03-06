<?php

namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\NotifyController;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
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
        $request->query->set('gateway', 'theGatewayName');

        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->method('execute')
            ->with($this->isInstanceOf(Notify::class));

        $registryMock = $this->createMock(Payum::class);
        $registryMock
            ->method('getGateway')
            ->with('theGatewayName')
            ->willReturn($gatewayMock);

        $this->httpRequestVerifierMock = $this->createMock(
            HttpRequestVerifierInterface::class
        );

        $this->payum = new Payum(
            $registryMock,
            $this->httpRequestVerifierMock,
            $this->createMock(GenericTokenFactoryInterface::class),
            $this->createMock(StorageInterface::class)
        );

        $controller = new NotifyController($this->payum);

        $response = $controller->doUnsafeAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }
}
