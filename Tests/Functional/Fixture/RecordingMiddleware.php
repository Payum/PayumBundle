<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\Functional\Fixture;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\Result;

final class RecordingMiddleware implements MiddlewareInterface
{
    /**
     * What each command saw of the request, taken while it ran: the request Payum hands out always reads
     * the one being served, so keeping the object itself would show a later one.
     *
     * @var list<array{path: string, query: array<string, mixed>}>
     */
    public static array $requests = [];

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $request = $context->httpRequest();
        self::$requests[] = ['path' => $request->getUri()->getPath(), 'query' => $request->getQueryParams()];

        return $next($command, $context);
    }
}
