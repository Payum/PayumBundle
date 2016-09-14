<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Cancel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CancelController extends PayumController
{
    public function doAction(Request $request)
    {
        $token = $this->getPayum()->getHttpRequestVerifier()->verify($request);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());
        $gateway->execute(new Cancel($token));
        
        $this->getPayum()->getHttpRequestVerifier()->invalidate($token);
        
        return $token->getAfterUrl() ?
            $this->redirect($token->getAfterUrl()) :
            new Response('', 204)
        ;
    }
}