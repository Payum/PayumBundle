<?php

namespace Payum\Bundle\PayumBundle;

use Payum\Bundle\PayumBundle\Reply\HttpResponse as SymfonyHttpResponse;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse as CoreSymfonyHttpResponse;
use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Response;

class ReplyToSymfonyResponseConverter
{
    /**
     * @return Response
     */
    public function convert(ReplyInterface $reply)
    {
        if ($reply instanceof SymfonyHttpResponse || $reply instanceof CoreSymfonyHttpResponse) {
            return $reply->getResponse();
        }

        if ($reply instanceof HttpResponse) {
            $headers = $reply->getHeaders();
            $headers['X-Status-Code'] = $reply->getStatusCode();

            return new Response($reply->getContent(), $reply->getStatusCode(), $headers);
        }

        $ro = new ReflectionObject($reply);

        throw new LogicException(
            sprintf('Cannot convert reply %s to http response.', $ro->getShortName()),
            0,
            $reply
        );
    }
}
