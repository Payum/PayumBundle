<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\Http;

use Payum\Bundle\PayumBundle\Http\CurrentServerRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentServerRequestTest extends TestCase
{
    public function testShouldFollowTheMainRequestFromOneRequestToTheNext(): void
    {
        $stack = new RequestStack();
        $request = new CurrentServerRequest($stack);

        // what a worker runtime does: one request after the other, on the same services
        $stack->push(Request::create('https://shop.test/payment/capture/first', 'POST', ['paid' => '1']));
        $this->assertSame('/payment/capture/first', $request->getUri()->getPath());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(['paid' => '1'], $request->getParsedBody());
        $stack->pop();

        $stack->push(Request::create('https://shop.test/payment/notify/second?id=7'));
        $this->assertSame('/payment/notify/second', $request->getUri()->getPath());
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame(['id' => '7'], $request->getQueryParams());
    }

    public function testShouldConvertARequestOnlyOnce(): void
    {
        $stack = new RequestStack();
        $stack->push(Request::create('https://shop.test/'));
        $request = new CurrentServerRequest($stack);

        $this->assertSame($request->getBody(), $request->getBody());
    }

    public function testShouldConvertAgainAfterAReset(): void
    {
        $stack = new RequestStack();
        $stack->push(Request::create('https://shop.test/'));
        $request = new CurrentServerRequest($stack);

        $body = $request->getBody();
        $request->reset();

        $this->assertNotSame($body, $request->getBody());
    }

    public function testShouldStandForAGetOfLocalhostWithoutARequest(): void
    {
        $request = new CurrentServerRequest(new RequestStack());

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('http://localhost/', (string) $request->getUri());
    }

    public function testShouldReturnTheConvertedRequestFromAWither(): void
    {
        $stack = new RequestStack();
        $stack->push(Request::create('https://shop.test/'));
        $request = new CurrentServerRequest($stack);

        $changed = $request->withAttribute('gateway', 'offline');

        $this->assertNotInstanceOf(CurrentServerRequest::class, $changed);
        $this->assertSame('offline', $changed->getAttribute('gateway'));
        $this->assertNull($request->getAttribute('gateway'));
    }
}
