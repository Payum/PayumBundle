<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\PayumCoreGatewayFactory;
use Payum\Bundle\PayumBundle\PayumVersion;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BuildConfigsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $configs = PayumVersion::supportsDependencyInjection() ?
            $this->buildContainerEntries($container) :
            $this->buildLegacyConfig($container);

        $builder = $container->getDefinition('payum.builder');
        if ($container->hasDefinition('twig')) {
            $config = ['twig.env' => new Reference('twig')];

            $builder->addMethodCall('addCoreGatewayFactoryConfig', [$config]);
        }

        if (false === empty($configs[0])) {
            $builder->addMethodCall('addCoreGatewayFactoryConfig', [$configs[0]]);
        }

        foreach ($configs[1] as $factoryName => $factoryConfig) {
            $builder->addMethodCall('addGatewayFactoryConfig', [$factoryName, $factoryConfig]);
        }

        foreach ($configs[2] as $gatewayName => $gatewayConfig) {
            $builder->addMethodCall('addGateway', [$gatewayName, $gatewayConfig]);
        }
    }

    /**
     * The tagged services as configuration entries of payum/core 1.x, where a service is referred to by
     * an "@id" string which ContainerAwareCoreGatewayFactory resolves at runtime.
     *
     * @return array{0: array<string, mixed>, 1: array<string, array<string, mixed>>, 2: array<string, array<string, mixed>>}
     */
    protected function buildLegacyConfig(ContainerBuilder $container): array
    {
        $configs = $this->processTagData($container->findTaggedServiceIds('payum.action'), 'payum.action.', 'payum.prepend_actions');
        $configs = array_replace_recursive(
            $configs,
            $this->processTagData($container->findTaggedServiceIds('payum.api'), 'payum.api.', 'payum.prepend_apis')
        );

        return array_replace_recursive(
            $configs,
            $this->processTagData($container->findTaggedServiceIds('payum.extension'), 'payum.extension.', 'payum.prepend_extensions')
        );
    }

    /**
     * The tagged services as entries of the gateway containers of payum/core 2.0 and later, where a
     * service is a real reference resolved while the container is built.
     *
     * @return array{0: array<string, mixed>, 1: array<string, array<string, mixed>>, 2: array<string, array<string, mixed>>}
     */
    protected function buildContainerEntries(ContainerBuilder $container): array
    {
        $configs = $this->processTagDataForContainer(
            $container->findTaggedServiceIds('payum.action'),
            PayumCoreGatewayFactory::SHARED_ACTIONS,
            PayumCoreGatewayFactory::ACTIONS
        );
        $configs = array_replace_recursive(
            $configs,
            $this->processTagDataForContainer(
                $container->findTaggedServiceIds('payum.api'),
                PayumCoreGatewayFactory::SHARED_APIS,
                PayumCoreGatewayFactory::APIS
            )
        );

        return array_replace_recursive(
            $configs,
            $this->processTagDataForContainer(
                $container->findTaggedServiceIds('payum.extension'),
                PayumCoreGatewayFactory::SHARED_EXTENSIONS,
                PayumCoreGatewayFactory::EXTENSIONS
            )
        );
    }

    /**
     * @param array<string, list<array<string, mixed>>> $tagData
     *
     * @return array{0: array<string, mixed>, 1: array<string, array<string, mixed>>, 2: array<string, array<string, mixed>>}
     */
    protected function processTagDataForContainer(array $tagData, string $sharedKey, string $scopedKey): array
    {
        $coreGatewayFactoryConfig = [];
        $gatewaysFactoriesConfigs = [];
        $gatewaysConfigs = [];

        foreach ($tagData as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $attributes = array_replace(['alias' => null, 'factory' => null, 'gateway' => null,  'all' => false, 'prepend' => false], $attributes);

                $entry = [
                    'service' => new Reference($serviceId),
                    'prepend' => (bool) $attributes['prepend'],
                ];

                if ($attributes['all']) {
                    $coreGatewayFactoryConfig[$sharedKey][] = $entry;
                } elseif ($attributes['factory']) {
                    $gatewaysFactoriesConfigs[$attributes['factory']][$scopedKey][] = $entry;
                } elseif ($attributes['gateway']) {
                    $gatewaysConfigs[$attributes['gateway']][$scopedKey][] = $entry;
                }
            }
        }

        return [$coreGatewayFactoryConfig, $gatewaysFactoriesConfigs, $gatewaysConfigs];
    }

    protected function processTagData(array $tagData, string $namePrefix, string $prependKey): array
    {
        $coreGatewayFactoryConfig = [];
        $gatewaysFactoriesConfigs = [];
        $gatewaysConfigs = [];

        foreach ($tagData as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $attributes = array_replace(['alias' => null, 'factory' => null, 'gateway' => null,  'all' => false, 'prepend' => false], $attributes);

                $name = $attributes['alias'] ?: $serviceId;
                $name = $namePrefix.$name;

                if ($attributes['all']) {
                    $coreGatewayFactoryConfig[$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        if (false === isset($coreGatewayFactoryConfig[$prependKey])) {
                            $coreGatewayFactoryConfig[$prependKey] = [];
                        }

                        /** @noinspection UnsupportedStringOffsetOperationsInspection */
                        $coreGatewayFactoryConfig[$prependKey][] = $name;
                    }
                } elseif ($attributes['factory']) {
                    if (false === isset($gatewaysFactoriesConfigs[$attributes['factory']])) {
                        $gatewaysFactoriesConfigs[$attributes['factory']] = [];
                    }

                    $gatewaysFactoriesConfigs[$attributes['factory']][$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        if (false === isset($gatewaysFactoriesConfigs[$attributes['factory']][$prependKey])) {
                            $gatewaysFactoriesConfigs[$attributes['factory']][$prependKey] = [];
                        }

                        $gatewaysFactoriesConfigs[$attributes['factory']][$prependKey][] = $name;
                    }
                } elseif ($attributes['gateway']) {
                    if (false === isset($gatewaysConfigs[$attributes['gateway']])) {
                        $gatewaysConfigs[$attributes['gateway']] = [];
                    }

                    $gatewaysConfigs[$attributes['gateway']][$name] = "@$serviceId";

                    if ($attributes['prepend']) {
                        if (false === isset($gatewaysConfigs[$attributes['gateway']][$prependKey])) {
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
