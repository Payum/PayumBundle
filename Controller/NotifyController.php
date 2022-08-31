<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Notify;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyController extends PayumController
{
    public function doUnsafeAction(Request $request): Response
    {
        $gateway = $this->payum->getGateway($request->get('gateway'));

        $gateway->execute(new Notify(null));

        return new Response('', 204);
    }

    public function doAction(Request $request): Response
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute(new Notify($token));

        return new Response('', 204);
    }
}
