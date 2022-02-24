<?php
namespace Payum\Bundle\PayumBundle\Tests\EventListener;

use Payum\Bundle\PayumBundle\EventListener\ReplyToHttpResponseListener;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\HttpRedirect;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class ReplyToHttpResponseListenerTest extends TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOneArgument(): void
    {
        $this->expectNotToPerformAssertions();
        new ReplyToHttpResponseListener($this->createReplyToSymfonyResponseConverterMock());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfExceptionNotInstanceOfReply(): void
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
    public function shouldSetResponseReturnedByConverterToEvent(): void
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
    public function shouldCallAllowCustomResponseCode(): void
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
     * @return MockObject|ReplyToSymfonyResponseConverter
     */
    protected function createReplyToSymfonyResponseConverterMock()
    {
        return $this->createMock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
    }

    /**
     * @return HttpKernelInterface|MockObject
     */
    protected function createHttpKernelMock()
    {
        return $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    }
}
