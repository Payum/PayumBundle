<?php
namespace Payum\Bundle\PayumBundle\Tests\EventListener;

use Payum\Bundle\PayumBundle\EventListener\ReplyToHttpResponseListener;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\HttpRedirect;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class ReplyToHttpResponseListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOneArgument()
    {
        new ReplyToHttpResponseListener($this->createReplyToSymfonyResponseConverterMock());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfExceptionNotInstanceOfReply()
    {
        $expectedException = new Exception;

        $event = new ExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            Kernel::MASTER_REQUEST,
            $expectedException
        );

        $converterMock = $this->createReplyToSymfonyResponseConverterMock();
        $converterMock
            ->expects($this->never())
            ->method('convert')
        ;

        $listener = new ReplyToHttpResponseListener($converterMock);

        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
        $this->assertSame($expectedException, $event->getThrowable());
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @test
     */
    public function shouldSetResponseReturnedByConverterToEvent()
    {
        $expectedUrl = '/foo/bar';

        $reply = new HttpRedirect($expectedUrl);
        $response = new Response();

        $event = new ExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            Kernel::MASTER_REQUEST,
            $reply
        );

        $converterMock = $this->createReplyToSymfonyResponseConverterMock();
        $converterMock
            ->expects($this->once())
            ->method('convert')
            ->with($this->identicalTo($reply))
            ->will($this->returnValue($response))
        ;

        $listener = new ReplyToHttpResponseListener($converterMock);

        $listener->onKernelException($event);

        $this->assertSame($response, $event->getResponse());
        $this->assertSame($reply, $event->getThrowable());
    }

    /**
     * @test
     */
    public function shouldCallAllowCustomResponseCode()
    {
        $reply = new HttpRedirect('/foo/bar');
        $response = new Response('', 302);

        $event = new ExceptionEvent($this->createHttpKernelMock(), new Request, Kernel::MASTER_REQUEST, $reply);

        $converterMock = $this->createReplyToSymfonyResponseConverterMock();
        $converterMock
            ->expects($this->once())
            ->method('convert')
            ->with($this->identicalTo($reply))
            ->will($this->returnValue($response))
        ;

        $listener = new ReplyToHttpResponseListener($converterMock);

        $listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
        $this->assertEquals(302, $event->getResponse()->getStatusCode());
        $this->assertEquals(true, $event->isAllowingCustomResponseCode());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReplyToSymfonyResponseConverter
     */
    protected function createReplyToSymfonyResponseConverterMock()
    {
        return $this->createMock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpKernelInterface
     */
    protected function createHttpKernelMock()
    {
        return $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    }
}
