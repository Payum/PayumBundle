<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Builder;

use Payum\Bundle\PayumBundle\PayumCoreGatewayFactory;
use function call_user_func_array;
use function func_get_args;

/**
 * Builds the core gateway factory of the bundle for payum/core 2.0 and later.
 *
 * Unlike its 1.x counterpart it does not need the container: services reach the factory as real
 * references resolved while the container is built, rather than as "@id" strings resolved at runtime.
 */
class PayumCoreGatewayFactoryBuilder
{
    public function __invoke()
    {
        return call_user_func_array([$this, 'build'], func_get_args());
    }

    public function build(array $defaultConfig): PayumCoreGatewayFactory
    {
        return new PayumCoreGatewayFactory($defaultConfig);
    }
}
