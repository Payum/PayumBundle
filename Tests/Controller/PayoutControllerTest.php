<?php
namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\PayoutController;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Payout;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PayoutControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController()
    {
        $rc = new \ReflectionClass(PayoutController::class);

        $this->assertTrue($rc->isSubclassOf(AbstractController::class));
    }

    /**
     * @test
     */
    public function shouldExecutePayoutRequest()
    {
        $this->initMocks();
        $controller = new PayoutController($this->payum);

        $response = $controller->doAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::AFTER_URL, $response->getTargetUrl());
    }

    protected function initGatewayMock()
    {
        $this->gatewayMock = $this->createMock(GatewayInterface::class);
        $this->gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Payout::class));

    }
}
