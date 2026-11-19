<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\Functional\Fixture;

use Payum\Bundle\PayumBundle\Attribute\AsPayumMiddleware;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\Result;

#[AsPayumMiddleware(priority: 2500)]
final class AttributedMiddleware implements MiddlewareInterface
{
    public static int $calls = 0;

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        ++self::$calls;

        return $next($command, $context);
    }
}
