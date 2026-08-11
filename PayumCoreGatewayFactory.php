<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle;

use Payum\Bundle\PayumBundle\Action\ObtainCreditCardAction;
use Payum\Core\Action\PrependActionInterface;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Extension\PrependExtensionInterface;
use Payum\Core\Gateway;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use function array_merge;
use function DI\get;
use function func_get_args;

/**
 * The core gateway factory of the bundle, for payum/core 2.0 and later.
 *
 * Two things it adds to the core one: the Symfony flavour of the actions the bundle ships, and the
 * services tagged payum.action, payum.api and payum.extension.
 *
 * Those tagged services reach a gateway through its container: BuildConfigsPass puts them in the
 * configuration of the core gateway factory (the ones tagged with all) and of the gateway or gateway
 * factory they belong to, from where Payum turns them into entries of the gateway container.
 *
 * They are deliberately not named payum.action.<alias> as in payum/core 1.x. Those ids are used by
 * payum/core itself for the actions it registers, so re-using them would mean registering some of them
 * twice; and a per-gateway entry replaces a same-named entry of the gateway factory rather than adding
 * to it, which would make a gateway lose every service tagged with all as soon as it had one of its own.
 */
class PayumCoreGatewayFactory extends CoreGatewayFactory
{
    /**
     * Services tagged with all, shared by every gateway.
     */
    public const SHARED_ACTIONS = 'payum.bundle.shared_actions';

    public const SHARED_APIS = 'payum.bundle.shared_apis';

    public const SHARED_EXTENSIONS = 'payum.bundle.shared_extensions';

    /**
     * Services tagged for one gateway or one gateway factory.
     */
    public const ACTIONS = 'payum.bundle.actions';

    public const APIS = 'payum.bundle.apis';

    public const EXTENSIONS = 'payum.bundle.extensions';

    public function configureContainer(): array
    {
        return array_merge(
            parent::configureContainer(),
            [
                // payum/core looks its actions up by class name. Point the ones the bundle replaces at
                // the Symfony implementation, so that a gateway gets that one instead.
                GetHttpRequestAction::class => get('payum.action.get_http_request'),

                'payum.template.obtain_credit_card' => '@PayumSymfonyBridge\\obtainCreditCard.html.twig',

                ObtainCreditCardAction::class => static function (ContainerInterface $container): ObtainCreditCardAction {
                    $action = new ObtainCreditCardAction(
                        $container->get(FormFactoryInterface::class),
                        $container->get('payum.template.obtain_credit_card')
                    );
                    $action->setRequestStack($container->get(RequestStack::class));

                    return $action;
                },
            ],
            // whatever the application configured still wins over the definitions above
            $this->defaultConfig
        );
    }

    public function getActions(): array
    {
        return array_merge(parent::getActions(), [
            ObtainCreditCardAction::class,
        ]);
    }

    public function createGateway(ContainerInterface $container): Gateway
    {
        $gateway = parent::createGateway(...func_get_args());

        foreach ($this->collect($container, self::SHARED_APIS, self::APIS) as [$api, $prepend]) {
            $gateway->addApi($api, $prepend);
        }

        foreach ($this->collect($container, self::SHARED_ACTIONS, self::ACTIONS) as [$action, $prepend]) {
            $gateway->addAction($action, $prepend || $action instanceof PrependActionInterface);
        }

        foreach ($this->collect($container, self::SHARED_EXTENSIONS, self::EXTENSIONS) as [$extension, $prepend]) {
            $gateway->addExtension($extension, $prepend || $extension instanceof PrependExtensionInterface);
        }

        return $gateway;
    }

    /**
     * The services of the given entries, as [service, prepend] pairs, in the order they were tagged.
     *
     * @return list<array{0: object, 1: bool}>
     */
    private function collect(ContainerInterface $container, string ...$ids): array
    {
        $services = [];

        foreach ($ids as $id) {
            if (! $container->has($id)) {
                continue;
            }

            /** @var array<array{service: object, prepend?: bool}> $entries */
            $entries = (array) $container->get($id);

            foreach ($entries as $entry) {
                if (! isset($entry['service']) || ! is_object($entry['service'])) {
                    continue;
                }

                $services[] = [$entry['service'], (bool) ($entry['prepend'] ?? false)];
            }
        }

        return $services;
    }
}
