<?php
namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\CaptureController;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;

class CaptureControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController()
    {
        $rc = new \ReflectionClass(CaptureController::class);

        $this->assertTrue($rc->isSubclassOf(AbstractController::class));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage This controller requires session to be started.
     */
    public function throwBadRequestIfSessionNotStartedOnDoSessionAction()
    {
        $this->registryMock = $this->createMock(RegistryInterface::class);
        $this->httpRequestVerifierMock = $this->createMock(
            HttpRequestVerifierInterface::class
        );

        $controller = new CaptureController();
        $controller->setContainer(new ServiceLocator(['payum' => function () { return $this->payum; }]));

        $request = Request::create('/');

        //guard
        $this->assertFalse($request->hasSession());

        $controller->doSessionTokenAction($request);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage This controller requires token hash to be stored in the session.
     */
    public function throwBadRequestIfSessionNotContainPayumTokenOnDoSessionAction()
    {
        $this->registryMock = $this->createMock(RegistryInterface::class);
        $this->httpRequestVerifierMock = $this->createMock(
            HttpRequestVerifierInterface::class
        );

        $controller = new CaptureController();
        $controller->setContainer(new ServiceLocator(['payum' => function () { return $this->payum; }]));

        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $controller->doSessionTokenAction($request);
    }

    /**
     * @test
     */
    public function shouldDoRedirectToCaptureWithTokenUrl()
    {
        $routerMock = $this->createMock(RouterInterface::class);
        $routerMock
            ->expects($this->any())
            ->method('generate')
            ->with('payum_capture_do', array(
                'payum_token' => 'theToken',
                'foo' => 'fooVal',
            ))
            ->will($this->returnValue('/payment/capture/theToken?foo=fooVal'))
        ;

        $locator = new ServiceLocator([
            'payum' => function () { return $this->payum; },
            'router' => function () use ($routerMock) { return $routerMock; }
        ]);

        $this->registryMock = $this->createMock(RegistryInterface::class);
        $this->httpRequestVerifierMock = $this->createMock(
            HttpRequestVerifierInterface::class
        );

        $controller = new CaptureController();
        $controller->setContainer($locator);

        $this->request = Request::create('/');
        $this->request->query->set('foo', 'fooVal');

        $this->request->setSession(new Session(new MockArraySessionStorage()));
        $this->request->getSession()->set('payum_token', 'theToken');

        $response = $controller->doSessionTokenAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/payment/capture/theToken?foo=fooVal', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldExecuteCaptureRequest()
    {
        $controller = new CaptureController();
        $controller->setContainer(new ServiceLocator(['payum' => function () { return $this->payum; }]));

        $response = $controller->doAction($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::AFTER_URL, $response->getTargetUrl());
    }

    protected function initGatewayMock()
    {
        $this->gatewayMock = $this->createMock(GatewayInterface::class);
        $this->gatewayMock
            ->expects($this->any())
            ->method('execute')
            ->with($this->isInstanceOf(Capture::class))
        ;

    }
}
