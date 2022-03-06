<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Authorize;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthorizeController extends PayumController
{
    public function doAction(Request $request): RedirectResponse
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());
        $gateway->execute(new Authorize($token));
        
        $this->payum->getHttpRequestVerifier()->invalidate($token);
        
        return $this->redirect($token->getAfterUrl());
    }
}