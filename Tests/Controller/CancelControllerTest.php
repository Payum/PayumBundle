<?php
namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\CancelController;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Cancel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class CancelControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController(): void
    {
        $rc = new \ReflectionClass(CancelController::class);

        $this->assertTrue($rc->isSubclassOf(AbstractController::class));
    }

    /**
     * @test
     */
    public function shouldExecuteCancelRequest(): void
    {
        $controller = new CancelController($this->payum);

        $response = $controller->doAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::AFTER_URL, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldExecuteCancelRequestWithoutAfterUrl(): void
    {
        $this->token->setAfterUrl(null);

        $controller = new CancelController($this->payum);

        $response = $controller->doAction($this->request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    protected function initGatewayMock(): void
    {
        $this->gatewayMock = $this->createMock(GatewayInterface::class);
        $this->gatewayMock
            ->method('execute')
            ->with($this->isInstanceOf(Cancel::class))
        ;
    }
}
