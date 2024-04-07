<?php

namespace Payum\Bundle\PayumBundle;

use Payum\Bundle\PayumBundle\DependencyInjection\ContainerAwareInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\ContainerAwareTrait;
use Payum\Core\Registry\AbstractRegistry;

/**
 * @template T of object
 * @extends AbstractRegistry<T>
 */
class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function getService($id): ?object
    {
        return $this->container->get($id);
    }
}
