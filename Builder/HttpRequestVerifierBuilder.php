<?php
namespace Payum\Bundle\PayumBundle\Builder;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;

/**
 * @deprecated  since 1.2 and will be removed in 2.0 use one from bridge
 */
class HttpRequestVerifierBuilder
{
    /**
     * @param StorageInterface $tokenStorage
     *
     * @return HttpRequestVerifierInterface
     */
    public function build(StorageInterface $tokenStorage)
    {
        return new HttpRequestVerifier($tokenStorage);
    }
}