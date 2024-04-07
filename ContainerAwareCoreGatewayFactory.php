<?php

namespace Payum\Bundle\PayumBundle;

use Payum\Bundle\PayumBundle\DependencyInjection\ContainerAwareInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\ContainerAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\CoreGatewayFactory;

class ContainerAwareCoreGatewayFactory extends CoreGatewayFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function buildClosures(ArrayObject $config): void
    {
        foreach ($config as $name => $value) {
            if (! $value || ! is_string($value)) {
                continue;
            }

            $match = [];
            if (preg_match('/^%(.*?)%$/', $value, $match)) {
                $config[$name] = $value = $this->container->getParameter($match[1]);
            }

            if ('@' === $value[0] && $this->container->has(substr($value, 1))) {
                $config[$name] = $value = $this->container->get(substr($value, 1));
            }
        }

        parent::buildClosures($config);
    }
}
