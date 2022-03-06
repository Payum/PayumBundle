<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

interface StorageFactoryInterface
{
    /**
     * @return string The payment serviceId
     */
    public function create(ContainerBuilder $container, string $modelClass, array $config): string;

    /**
     * The storage name,
     * For example filesystem, doctrine, propel etc.
     */
    public function getName(): string;

    public function addConfiguration(ArrayNodeDefinition $builder): void;
}
