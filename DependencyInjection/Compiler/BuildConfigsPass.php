<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildConfigsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configs = $this->processTagData($container->findTaggedServiceIds('payum.action'), 'payum.action.', 'payum.prepend_actions');
        $configs = array_replace_recursive(
            $configs,
            $this->processTagData($container->findTaggedServiceIds('payum.api'), 'payum.api.', 'payum.prepend_apis')
        );
        $configs = array_replace_recursive(
            $configs,
            $this->processTagData($container->findTaggedServiceIds('payum.extension'), 'payum.extension.', 'payum.prepend_extensions')
        );

        $builder = $container->getDefinition('payum.builder');
        if ($container->hasDefinition('twig')) {
            $config = ['twig.env' => '@twig'];

            $builder->addMethodCall('addCoreGatewayFactoryConfig', [$config]);
        }

        if (false == empty($configs[0])) {
            $builder->addMethodCall('addCoreGatewayFactoryConfig', [$configs[0]]);
        }

        foreach ($configs[1] as $factoryName => $factoryConfig) {
            $builder->addMethodCall('addGatewayFactoryConfig', [$factoryName, $factoryConfig]);
        }

        foreach ($configs[2] as $gatewayName => $gatewayConfig) {
            $builder->addMethodCall('addGateway', [$gatewayName, $gatewayConfig]);
        }
    }

    protected function processTagData(array $tagData, $namePrefix, $prependKey)
    {
        $coreGatewayFactoryConfig = [];
        $gatewaysFactoriesConfigs = [];
        $gatewaysConfigs = [];

        foreach ($tagData as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $attributes = array_replace(['alias' => null, 'factory' => null, 'gateway' => null,  'all' => false, 'prepend' => false], $attributes);

                $name = $attributes['alias'] ?: $serviceId;
                $name = $namePrefix.$name;

                if ($attributes['all']) {
                    $coreGatewayFactoryConfig[$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        if (false == isset($coreGatewayFactoryConfig[$prependKey])) {
                            $coreGatewayFactoryConfig[$prependKey] = [];
                        }

                        $coreGatewayFactoryConfig[$prependKey][] = $name;
                    }
                } elseif ($attributes['factory']) {
                    if (false == isset($gatewaysFactoriesConfigs[$attributes['factory']])) {
                        $gatewaysFactoriesConfigs[$attributes['factory']] = [];
                    }

                    $gatewaysFactoriesConfigs[$attributes['factory']][$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        if (false == isset($gatewaysFactoriesConfigs[$attributes['factory']][$prependKey])) {
                            $gatewaysFactoriesConfigs[$attributes['factory']][$prependKey] = [];
                        }

                        $gatewaysFactoriesConfigs[$attributes['factory']][$prependKey][] = $name;
                    }
                } elseif ($attributes['gateway']) {
                    if (false == isset($gatewaysConfigs[$attributes['gateway']])) {
                        $gatewaysConfigs[$attributes['gateway']] = [];
                    }

                    $gatewaysConfigs[$attributes['gateway']][$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        if (false == isset($gatewaysConfigs[$attributes['gateway']][$prependKey])) {
                            $gatewaysConfigs[$attributes['gateway']][$prependKey] = [];
                        }

                        $gatewaysConfigs[$attributes['gateway']][$prependKey][] = $name;
                    }
                }
            }
        }

        return [$coreGatewayFactoryConfig, $gatewaysFactoriesConfigs, $gatewaysConfigs];
    }
}
