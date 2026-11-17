# Container tags

The bundle supports\reuse several Symfony container tags. You may find them useful too.

## Gateway config tag (payum/core 2.0)

The tag `payum.gateway_config` registers a gateway from a service implementing
`Payum\Core\Config\GatewayConfig`. The `gateway` attribute is the gateway name, and it must not also be
used under `payum.gateways`. Two services of the same class register two gateways:

```yaml
# config/services.yaml

services:
    app.payum.offline_eu:
        class: Payum\Offline\OfflineConfig
        arguments: { $paid: '%env(bool:OFFLINE_EU_PAID)%' }
        tags:
            - { name: payum.gateway_config, gateway: offline_eu }

    app.payum.offline_us:
        class: Payum\Offline\OfflineConfig
        arguments: { $paid: false }
        tags:
            - { name: payum.gateway_config, gateway: offline_us }
```

## Gateway tag

The tag `payum.gateway` could be used if you want to register your service as a gateway gateway. 
The service must implement `Payum\Core\GatewayInterface`.

```yaml
# config/packages/payum.yml

services:
    acme.foo_gateway:
        class: Payum\Core\Gateway
        public: true
        tags:
            - { name: payum.gateway, gateway: foo }
```

Attributes:

* factory - define if you want later add actions or extensions to all gateways created by this factory.

* gateway - define if you want later add actions or extensions to this gateway.

## Action tag

The tag `payum.action` could be used if you want to register your service as an action. 
The service must implement `Payum\Core\Action\ActionInterface`.

```yaml
# config/packages/payum.yml

services:
    acme.foo_action:
        class: Payum\Core\Action\ActionInterface
        public: true
        tags:
            - { name: payum.action, factory: foo, gateway: bar, all: true, alias: foo, prepend: false }
```

Attributes:

* factory - define if you want to add the action to gateways created by the factory with given name.

* gateway - define if you want to add the action to a gateway with given name

* all - define if you want to add the action to all gateways

* prepend - define if want your action to be put at the begging.

* alias - you can use alias if you' like to overwrite the default action or add a good looking name

## Api tag

The tag `payum.api` could be used if you want to register your service as an api.
The service could be any object.

```yaml
# config/packages/payum.yml

services:
    acme.foo_api:
        class: Acme\Payum\FooApi
        public: true
        tags:
            - { name: payum.api, factory: foo, gateway: bar, all: true, alias: foo, prepend: false }
```

Attributes:

* factory - define if you want to add the extension to gateways created by the factory with given name.

* gateway - define if you want to add the extension to a gateway with given name

* all - define if you want to add the extension to all gateways

* prepend - define if want your extension to be put at the begging.

* alias - you can use alias if you' like to overwrite the default action or add a good looking name

## Extension tag

The tag `payum.extension` could be used if you want to register your service as an extension. 
The service must implement `Payum\Core\Extension\ExtensionInterface`.

```yaml
# config/packages/payum.yml

services:
    acme.foo_extension:
        class: Payum\Core\Extension\ExtensionInterface
        public: true
        tags:
            - { name: payum.extension, factory: foo, gateway: bar, all: true, alias: foo, prepend: false }
```

Attributes:

* factory - define if you want to add the extension to gateways created by the factory with given name.

* gateway - define if you want to add the extension to a gateway with given name

* all - define if you want to add the extension to all gateways

* prepend - define if want your extension to be put at the begging.

* alias - you can use alias if you' like to overwrite the default action or add a good looking name

## Gateway factory tag

The tag `payum.gateway_factory` could be used if you want to register your service as a gateway factory. 
The service must implement `Payum\Core\GatewayFactoryInterface`.

```yaml
# config/packages/payum.yml

services:
    acme.foo_gateway_factory:
        class: Payum\Core\GatewayFactory
        public: true
        tags:
            - { name: payum.gateway_factory, factory: foo }
```

Attributes:

* name - The name of the factory

* human_name - The name shown to humans, in the backend for example.

## Next Step

* [Get it started](get_it_started.md).
* [Custom purchase examples](custom_purchase_examples.md).
* [Back to index](index.md).

