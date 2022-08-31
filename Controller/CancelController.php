<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Cancel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CancelController extends PayumController
{
    /**
     * @throws \Exception
     */
    public function doAction(Request $request): Response
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());
        $gateway->execute(new Cancel($token));

        $this->payum->getHttpRequestVerifier()->invalidate($token);

        return $token->getAfterUrl() ?
            $this->redirect($token->getAfterUrl()) :
            new Response('', 204)
        ;
    }
}
