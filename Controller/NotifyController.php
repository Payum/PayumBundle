<?php

namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Request\SecuredNotifyRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyController extends PayumController
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
        $payment->execute(new SecuredNotifyRequest(
            array_replace($request->query->all(), $request->request->all()),
            $token
        ));

        return new Response('', 204);
    }
}
