<?php
namespace Payum\Bundle\PayumBundle\Tests\EventListener;

use Payum\Bundle\PayumBundle\EventListener\ReplyToHttpResponseListener;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\HttpRedirect;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
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

        $event = new GetResponseForExceptionEvent(
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
        $this->assertSame($expectedException, $event->getException());
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

        $event = new GetResponseForExceptionEvent(
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
        $this->assertSame($reply, $event->getException());
    }

    /**
     * @test
     */
    public function shouldSetXStatusFromResponseStatusCode()
    {
        $reply = new HttpRedirect('/foo/bar');
        $response = new Response('', 302);

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            Kernel::MASTER_REQUEST,
            $reply
        );

        // BC for SF < 3.3
        if (method_exists($event, 'allowCustomResponseCode')) {
            $this->markTestSkipped('BC for SF < 3.3');
        }

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
        $this->assertTrue($event->getResponse()->headers->has('X-Status-Code'));
        $this->assertEquals(302, $event->getResponse()->headers->get('X-Status-Code'));
    }

    /**
     * @test
     */
    public function shouldNotSetXStatusIfAlreadySet()
    {
        $reply = new HttpRedirect('/foo/bar');
        $response = new Response('', 555, array(
            'X-Status-Code' => 666,
        ));

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            Kernel::MASTER_REQUEST,
            $reply
        );

        // BC for SF < 3.3
        if (method_exists($event, 'allowCustomResponseCode')) {
            $this->markTestSkipped('BC for SF < 3.3');
        }

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
        $this->assertTrue($event->getResponse()->headers->has('X-Status-Code'));
        $this->assertEquals(666, $event->getResponse()->headers->get('X-Status-Code'));
    }

    /**
     * @test
     */
    public function shouldCallAllowCustomResponseCode()
    {
        $reply = new HttpRedirect('/foo/bar');
        $response = new Response('', 302);

        $eventMock = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->setMethods(null)
            ->setConstructorArgs([$this->createHttpKernelMock(),
                new Request,
                Kernel::MASTER_REQUEST,
                $reply])
            ->getMock();

        // BC for SF < 3.3
        if (!method_exists($eventMock, 'allowCustomResponseCode')) {
            $this->markTestSkipped('BC for SF < 3.3');
        }

        $converterMock = $this->createReplyToSymfonyResponseConverterMock();
        $converterMock
            ->expects($this->once())
            ->method('convert')
            ->with($this->identicalTo($reply))
            ->will($this->returnValue($response))
        ;

        $eventMock
            ->expects($this->once())
            ->method('allowCustomResponseCode')
        ;

        $listener = new ReplyToHttpResponseListener($converterMock);

        $listener->onKernelException($eventMock);

        $this->assertInstanceOf(Response::class, $eventMock->getResponse());
        $this->assertEquals(302, $eventMock->getResponse()->getStatusCode());
        $this->assertEquals(true, $eventMock->isAllowingCustomResponseCode());
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
