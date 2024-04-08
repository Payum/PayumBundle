<?php

namespace Payum\Bundle\PayumBundle\Tests\Reply;

use Payum\Bundle\PayumBundle\Reply\HttpResponse;
use Payum\Core\Reply\Base;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

class HttpResponseTest extends TestCase
{
    public function testShouldBeSubClassOfBaseReply(): void
    {
        $rc = new ReflectionClass(HttpResponse::class);

        $this->assertTrue($rc->isSubclassOf(Base::class));
    }

    public function testShouldAllowGetResponseSetInConstructor(): void
    {
        $expectedResponse = new Response();

        $request = new HttpResponse($expectedResponse);

        $this->assertSame($expectedResponse, $request->getResponse());
    }
}
