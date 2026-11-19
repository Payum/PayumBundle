<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Exception\ConcurrentOperationException;
use Payum\Core\Request\Authorize;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthorizeController extends PayumController
{
    public function doAction(Request $request): RedirectResponse
    {
        $token = $this->getPayum()->getHttpRequestVerifier()->verify($request);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        try {
            $gateway->execute(new Authorize($token));
        } catch (ConcurrentOperationException) {
            // payum/core 2.x locking: another request is already finishing this payment, and will
            // invalidate the token. Nothing was sent to the PSP, so show the customer the outcome.
            return $this->redirect($token->getAfterUrl());
        }

        $this->getPayum()->getHttpRequestVerifier()->invalidate($token);

        return $this->redirect($token->getAfterUrl());
    }
}
