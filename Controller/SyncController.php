<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Sync;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncController extends PayumController
{
    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        $gateway->execute(new Sync($token));

        if (false == $request->query->get('noinvalidate')) {
            $this->getHttpRequestVerifier()->invalidate($token);
        }

        return $token->getAfterUrl() ?
            $this->redirect($token->getAfterUrl()) :
            new Response('', 204)
        ;
    }
}