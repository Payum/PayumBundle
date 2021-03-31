<?php

namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\RefundController;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Refund;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RefundControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController()
    {
        $rc = new \ReflectionClass(RefundController::class);

        $this->assertTrue($rc->isSubclassOf(AbstractController::class));
    }

    /**
     * @test
     */
    public function shouldExecuteRefundRequest()
    {
        $controller = new RefundController();
        $controller->setContainer(new ServiceLocator(['payum' => function () { return $this->payum; }]));

        $response = $controller->doAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::AFTER_URL, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldExecuteRefundRequestWithoutAfterUrl()
    {
        $this->token->setAfterUrl(null);

        $controller = new RefundController();
        $controller->setContainer(new ServiceLocator(['payum' => function () { return $this->payum; }]));

        $response = $controller->doAction($this->request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    protected function initGatewayMock()
    {
        $this->gatewayMock = $this->createMock(GatewayInterface::class);
        $this->gatewayMock
            ->expects($this->any())
            ->method('execute')
            ->with($this->isInstanceOf(Refund::class));

    }
}
