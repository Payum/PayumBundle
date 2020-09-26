<?php
namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\CancelController;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Cancel;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CancelControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController()
    {
        $rc = new \ReflectionClass(CancelController::class);

        $this->assertTrue($rc->isSubclassOf(AbstractController::class));
    }

    /**
     * @test
     */
    public function shouldExecuteCancelRequest()
    {
        $this->initMocks();
        $controller = new CancelController($this->payum);

        $response = $controller->doAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::AFTER_URL, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldExecuteCancelRequestWithoutAfterUrl()
    {
        $this->initMocks();
        $this->token->setAfterUrl(null);

        $controller = new CancelController($this->payum);

        $response = $controller->doAction($this->request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    protected function initGatewayMock()
    {
        $this->gatewayMock = $this->createMock(GatewayInterface::class);
        $this->gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Cancel::class))
        ;
    }
}
