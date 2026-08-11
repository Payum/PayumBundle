<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\DI;

use Payum\Core\DI\ListableContainerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;
use function array_keys;
use function interface_exists;

/**
 * Payum's global container, backed by the services of the application.
 *
 * Payum is handed a service locator rather than the whole Symfony container: it only ever sees the
 * services the bundle decided to share, so a lookup of a service Payum defines itself is not
 * accidentally answered by an unrelated service of the application which happens to have the same id.
 *
 * Reporting the entries of the locator is what makes those services injectable into the constructor of
 * a gateway action - Payum can only turn services it knows the ids of into definitions of the gateway
 * containers. That is what ListableContainerInterface is for. It only exists from payum/core 2.0.1
 * onwards, which is why the class is declared twice below: with an older 2.x the services are still
 * resolvable from a gateway, they just cannot be autowired.
 */
trait SymfonyContainerAdapterTrait
{
    public function __construct(
        private ServiceProviderInterface $locator
    ) {
    }

    public function get(string $id): mixed
    {
        return $this->locator->get($id);
    }

    public function has(string $id): bool
    {
        return $this->locator->has($id);
    }

    /**
     * @return list<string>
     */
    public function getKnownEntryNames(): array
    {
        return array_keys($this->locator->getProvidedServices());
    }
}

if (interface_exists(ListableContainerInterface::class)) {
    final class SymfonyContainerAdapter implements ListableContainerInterface
    {
        use SymfonyContainerAdapterTrait;
    }
} else {
    final class SymfonyContainerAdapter implements ContainerInterface
    {
        use SymfonyContainerAdapterTrait;
    }
}
