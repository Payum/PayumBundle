<?php

namespace Payum\Bundle\PayumBundle\Tests\Builder;

use Payum\Bundle\PayumBundle\Builder\HttpRequestVerifierBuilder;
use Payum\Bundle\PayumBundle\Security\HttpRequestVerifier;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

class HttpRequestVerifierBuilderTest extends TestCase
{
    public function testShouldBuildSymfonyHttpRequestVerifier(): void
    {
        /** @var StorageInterface<TokenInterface> $tokenStorage */
        $tokenStorage = $this->createMock(StorageInterface::class);

        $builder = new HttpRequestVerifierBuilder();

        $verifier = $builder->build($tokenStorage);

        $this->assertInstanceOf(HttpRequestVerifier::class, $verifier);
    }

    public function testAllowUseBuilderAsAsFunction(): void
    {
        /** @var StorageInterface<object> $tokenStorage */
        $tokenStorage = $this->createMock(StorageInterface::class);

        $builder = new HttpRequestVerifierBuilder();

        $verifier = $builder($tokenStorage);

        $this->assertInstanceOf(HttpRequestVerifier::class, $verifier);
    }
}
