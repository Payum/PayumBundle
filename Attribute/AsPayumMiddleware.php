<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Attribute;

use Attribute;

/**
 * Registers a payum/core 2.x middleware, so it wraps every command on every gateway:
 *
 *     #[AsPayumMiddleware(priority: 1500)]
 *     final class RefundLimitMiddleware implements MiddlewareInterface
 *
 * Without a priority, the middleware's own HasPriority applies, else 0. Higher runs further out.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AsPayumMiddleware
{
    public function __construct(
        public readonly ?int $priority = null,
    ) {
    }
}
