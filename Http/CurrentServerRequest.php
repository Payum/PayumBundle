<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Hands payum/core 2.x the request Symfony is serving, as PSR-7.
 *
 * Without it Payum builds the request from PHP's globals, which are empty in a functional test and stale
 * in a worker runtime. Payum keeps the first request it gets for the life of each gateway, so this object
 * never holds a request itself: every call reads the main request off the request stack, converted once
 * per request. In a worker (FrankenPHP, RoadRunner) every request therefore sees its own, and kernel.reset
 * drops the converted copy between requests.
 *
 * On the command line, where there is no request, it stands for an empty GET of http://localhost/.
 *
 * Scalar parameters are left untyped so the class satisfies both psr/http-message 1.x and 2.x.
 */
final class CurrentServerRequest implements ServerRequestInterface, ResetInterface
{
    private readonly HttpMessageFactoryInterface $httpMessageFactory;

    private ?Request $request = null;

    private ?ServerRequestInterface $converted = null;

    private ?Request $commandLineRequest = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        ?HttpMessageFactoryInterface $httpMessageFactory = null,
    ) {
        $this->httpMessageFactory = $httpMessageFactory ?? new PsrHttpFactory();
    }

    public function reset(): void
    {
        $this->request = null;
        $this->converted = null;
        $this->commandLineRequest = null;
    }

    private function current(): ServerRequestInterface
    {
        $request = $this->requestStack->getMainRequest() ?? $this->commandLineRequest ??= Request::create('/');

        if ($request !== $this->request || null === $this->converted) {
            $this->request = $request;
            $this->converted = $this->httpMessageFactory->createRequest($request);
        }

        return $this->converted;
    }

    public function getProtocolVersion(): string
    {
        return $this->current()->getProtocolVersion();
    }

    public function withProtocolVersion($version): MessageInterface
    {
        return $this->current()->withProtocolVersion($version);
    }

    public function getHeaders(): array
    {
        return $this->current()->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->current()->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->current()->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->current()->getHeaderLine($name);
    }

    public function withHeader($name, $value): MessageInterface
    {
        return $this->current()->withHeader($name, $value);
    }

    public function withAddedHeader($name, $value): MessageInterface
    {
        return $this->current()->withAddedHeader($name, $value);
    }

    public function withoutHeader($name): MessageInterface
    {
        return $this->current()->withoutHeader($name);
    }

    public function getBody(): StreamInterface
    {
        return $this->current()->getBody();
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->current()->withBody($body);
    }

    public function getRequestTarget(): string
    {
        return $this->current()->getRequestTarget();
    }

    public function withRequestTarget($requestTarget): RequestInterface
    {
        return $this->current()->withRequestTarget($requestTarget);
    }

    public function getMethod(): string
    {
        return $this->current()->getMethod();
    }

    public function withMethod($method): RequestInterface
    {
        return $this->current()->withMethod($method);
    }

    public function getUri(): UriInterface
    {
        return $this->current()->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        return $this->current()->withUri($uri, $preserveHost);
    }

    public function getServerParams(): array
    {
        return $this->current()->getServerParams();
    }

    public function getCookieParams(): array
    {
        return $this->current()->getCookieParams();
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return $this->current()->withCookieParams($cookies);
    }

    public function getQueryParams(): array
    {
        return $this->current()->getQueryParams();
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        return $this->current()->withQueryParams($query);
    }

    public function getUploadedFiles(): array
    {
        return $this->current()->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return $this->current()->withUploadedFiles($uploadedFiles);
    }

    public function getParsedBody()
    {
        return $this->current()->getParsedBody();
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        return $this->current()->withParsedBody($data);
    }

    public function getAttributes(): array
    {
        return $this->current()->getAttributes();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->current()->getAttribute($name, $default);
    }

    public function withAttribute($name, $value): ServerRequestInterface
    {
        return $this->current()->withAttribute($name, $value);
    }

    public function withoutAttribute($name): ServerRequestInterface
    {
        return $this->current()->withoutAttribute($name);
    }
}
