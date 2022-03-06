<?php

namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractControllerTest extends TestCase
{
    protected const GATEWAY_NAME = 'theGateway';
    protected const AFTER_URL = 'http://example.com/theAfterUrl';

    protected Token $token;

    /** @var RegistryInterface&MockObject */
    protected $httpRequestVerifierMock;

    /** @var GatewayInterface&MockObject */
    protected $gatewayMock;

    /** @var RegistryInterface&MockObject */
    protected $registryMock;

    protected Payum $payum;
    protected Request $request;

    protected function setUp(): void
    {
        $this->request = Request::create('/');
        $this->request->query->set('foo', 'fooVal');

        $this->token = new Token;
        $this->token->setGatewayName(self::GATEWAY_NAME);
        $this->token->setAfterUrl(self::AFTER_URL);

        $this->httpRequestVerifierMock = $this->createMock(
            HttpRequestVerifierInterface::class
        );
        $this->httpRequestVerifierMock
            ->method('verify')
            ->with($this->identicalTo($this->request))
            ->willReturn($this->token);

        $this->httpRequestVerifierMock
            ->method('invalidate')
            ->with($this->identicalTo($this->token));

        $this->initGatewayMock();

        $this->registryMock = $this->createMock(RegistryInterface::class);
        $this->registryMock
            ->method('getGateway')
            ->with(self::GATEWAY_NAME)
            ->willReturn($this->gatewayMock);

        $this->payum = new Payum(
            $this->registryMock,
            $this->httpRequestVerifierMock,
            $this->createMock(GenericTokenFactoryInterface::class),
            $this->createMock(StorageInterface::class)
        );
    }

    abstract protected function initGatewayMock();
}
