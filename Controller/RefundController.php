<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Refund;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RefundController extends PayumController
{
    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());
        $gateway->execute(new Refund($token));

        if (false == $request->query->get('noinvalidate')) {
            $this->getHttpRequestVerifier()->invalidate($token);
        }
        
        return $token->getAfterUrl() ?
            $this->redirect($token->getAfterUrl()) :
            new Response('', 204)
        ;
    }
}