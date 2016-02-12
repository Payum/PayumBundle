<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildConfigsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configs = $this->processTagData($container->findTaggedServiceIds('payum.action'), 'payum.action.');
        $configs = array_replace_recursive(
            $configs,
            $this->processTagData($container->findTaggedServiceIds('payum.api'), 'payum.api.')
        );
        $configs = array_replace_recursive(
            $configs,
            $this->processTagData($container->findTaggedServiceIds('payum.extension'), 'payum.extension.')
        );


        $builder = $container->getDefinition('payum.builder');
        $builder->addMethodCall('addCoreGatewayFactoryConfig', [$configs[0]]);

        foreach ($configs[1] as $factoryName => $factoryConfig) {
            $builder->addMethodCall('addGatewayFactoryConfig', [$factoryName, $factoryConfig]);
        }

        foreach ($configs[2] as $gatewayName => $gatewayConfig) {
            $builder->addMethodCall('addGateway', [$gatewayName, $gatewayConfig]);
        }
    }

    protected function processTagData(array $tagData, $namePrefix)
    {
        $coreGatewayFactoryConfig = [
            'payum.prepend_actions' => [],
            'payum.prepend_apis' => [],
            'payum.prepend_extensions' => [],
        ];
        $gatewaysFactoriesConfigs = [
            'payum.prepend_actions' => [],
            'payum.prepend_apis' => [],
            'payum.prepend_extensions' => [],
        ];
        $gatewaysConfigs = [
            'payum.prepend_actions' => [],
            'payum.prepend_apis' => [],
            'payum.prepend_extensions' => [],
        ];

        foreach ($tagData as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $attributes = array_replace(['alias' => null, 'factory' => null, 'gateway' => null,  'all' => true, 'prepend' => false], $attributes);

                $name = $attributes['alias'] ?: $serviceId;
                $name = $namePrefix.$name;

                if ($attributes['all']) {
                    $coreGatewayFactoryConfig[$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        $coreGatewayFactoryConfig['payum.prepend_actions'][] = $name;
                    }
                } elseif ($attributes['factory']) {
                    if (false == isset($gatewaysFactoriesConfigs[$attributes['factory']])) {
                        $gatewaysFactoriesConfigs[$attributes['factory']] = [];
                    }

                    $gatewaysFactoriesConfigs[$attributes['factory']][$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        $gatewaysFactoriesConfigs[$attributes['factory']]['payum.prepend_actions'][] = $name;
                    }
                } elseif ($attributes['gateway']) {
                    if (false == isset($gatewaysConfigs[$attributes['gateway']])) {
                        $gatewaysConfigs[$attributes['gateway']] = [];
                    }

                    $gatewaysConfigs[$attributes['gateway']][$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        $gatewaysConfigs[$attributes['gateway']]['payum.prepend_actions'][] = $name;
                    }
                }
            }
        }

        return [$coreGatewayFactoryConfig, $gatewaysFactoriesConfigs, $gatewaysConfigs];
    }
}
