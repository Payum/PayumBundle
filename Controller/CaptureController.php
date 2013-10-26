<?php

namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\SecuredCaptureRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CaptureController extends PayumController
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws HttpException
     */
    public function doAction(Request $request)
    {
        $token   = $this->getHttpRequestVerifier()->verify($request);

        $status  = new BinaryMaskStatusRequest($token);
        $payment = $this->getPayum()->getPayment($token->getPaymentName());
        $payment->execute($status);
        if (!$status->isNew()) {
            throw new HttpException(400, 'The model status must be new.');
        }

        $payment->execute(new SecuredCaptureRequest($token));

        $this->getHttpRequestVerifier()->invalidate($token);

        return new RedirectResponse($token->getAfterUrl(), 302);
    }
}
