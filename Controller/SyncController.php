<?php

namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Exception\RequestNotSupportedException;
use Payum\Request\SyncRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SyncController extends PayumController
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function doAction(Request $request)
    {
        $token   = $this->getHttpRequestVerifier()->verify($request);

        $payment = $this->getPayum()->getPayment($token->getPaymentName());
        $payment->execute(new SyncRequest($token));

        $this->getHttpRequestVerifier()->invalidate($token);

        return new RedirectResponse($token->getAfterUrl(), 302);
    }
}
