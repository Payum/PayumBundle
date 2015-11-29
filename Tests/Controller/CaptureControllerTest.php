<?php
namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\CaptureController;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;

class CaptureControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController()
    {
        $rc = new \ReflectionClass(CaptureController::class);

        $this->assertTrue($rc->isSubclassOf(Controller::class));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage This controller requires session to be started.
     */
    public function throwBadRequestIfSessionNotStartedOnDoSessionAction()
    {
        $controller = new CaptureController;

        $request = Request::create('/');

        //guard
        $this->assertNull($request->getSession());

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
        $controller = new CaptureController;

        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $controller->doSessionTokenAction($request);
    }

    /**
     * @test
     */
    public function shouldDoRedirectToCaptureWithTokenUrl()
    {
        $routerMock = $this->getMock(RouterInterface::class);
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('payum_capture_do', array(
                'payum_token' => 'theToken',
                'foo' => 'fooVal',
            ))
            ->will($this->returnValue('/payment/capture/theToken?foo=fooVal'))
        ;

        $container = new Container;
        $container->set('router', $routerMock);

        $controller = new CaptureController;
        $controller->setContainer($container);

        $request = Request::create('/');
        $request->query->set('foo', 'fooVal');

        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->getSession()->set('payum_token', 'theToken');

        $response = $controller->doSessionTokenAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/payment/capture/theToken?foo=fooVal', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldExecuteCaptureRequest()
    {
        $request = Request::create('/');
        $request->query->set('foo', 'fooVal');

        $token = new Token;
        $token->setGatewayName('theGateway');
        $token->setAfterUrl('http://example.com/theAfterUrl');

        $httpRequestVerifierMock = $this->getMock(HttpRequestVerifierInterface::class);
        $httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->with($this->identicalTo($request))
            ->will($this->returnValue($token))
        ;
        $httpRequestVerifierMock
            ->expects($this->once())
            ->method('invalidate')
            ->with($this->identicalTo($token))
        ;

        $gatewayMock = $this->getMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Capture::class))
        ;

        $registryMock = $this->getMock(RegistryInterface::class);
        $registryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGateway')
            ->will($this->returnValue($gatewayMock))
        ;

        $payum = new Payum(
            $registryMock,
            $httpRequestVerifierMock,
            $this->getMock(GenericTokenFactoryInterface::class),
            $this->getMock(StorageInterface::class)
        );

        $container = new Container;
        $container->set('payum', $payum);

        $controller = new CaptureController;
        $controller->setContainer($container);

        $response = $controller->doAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://example.com/theAfterUrl', $response->getTargetUrl());
    }
}
