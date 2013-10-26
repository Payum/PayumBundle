<?php
namespace Payum\Bundle\PayumBundle\Security;

use Payum\Exception\InvalidArgumentException;
use Payum\Model\Token;
use Payum\Model\TokenizedDetails;
use Payum\Security\HttpRequestVerifierInterface;
use Payum\Security\TokenInterface;
use Payum\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpRequestVerifier implements HttpRequestVerifierInterface
{
    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     * @param StorageInterface $tokenStorage
     */
    public function __construct(StorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function verify($httpRequest)
    {
        if (!$httpRequest instanceof Request) {
            throw new InvalidArgumentException(sprintf(
                'Invalid request given. Expected %s but it is %s',
                'Symfony\Component\HttpFoundation\Request',
                is_object($httpRequest) ? get_class($httpRequest) : gettype($httpRequest)
            ));
        }

        if (!$hash = $httpRequest->attributes->get('payum_token', $httpRequest->get('payum_token', null))) {
            throw new NotFoundHttpException('Token parameter not set in request');
        }

        if ($hash instanceof Token) {
            $token = $hash;
        } else {
            if (!$token = $this->tokenStorage->findModelById($hash)) {
                throw new NotFoundHttpException(sprintf('A token with hash `%s` could not be found.', $hash));
            }

            if (parse_url($httpRequest->getUri(), PHP_URL_PATH) != parse_url($token->getTargetUrl(), PHP_URL_PATH)) {
                throw new HttpException(400, sprintf('The current url %s not match target url %s set in the token.', $httpRequest->getUri(), $token->getTargetUrl()));
            }
        }

        return $token;
    }

    /**
     * {@inheritDoc}
     */
    public function invalidate(TokenInterface $token)
    {
        $this->tokenStorage->deleteModel($token);
    }
}
