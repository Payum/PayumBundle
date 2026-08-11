# Upgrades

Library [upgrades](https://github.com/Payum/Payum/blob/master/UPGRADE.md).

## Payum 2.0

The bundle works with both payum/core 1.x and payum/core 2.0. Which one is installed is detected at
compile time, and the services which differ between the two are loaded from
`Resources/config/payum_v1.php` or `Resources/config/payum_v2.php` accordingly. Nothing has to be
changed in an application to keep running on 1.x.

What changes when payum/core 2.0 is installed:

* Payum builds every gateway with a dependency injection container. The bundle hands it the services of
  the application as its global container (`payum.di.global_container`), so that a gateway can resolve
  them and, when payum/core is able to be told a container lists its entries, inject them into the
  constructor of an action.
* Any service tagged `payum.global_service` is shared with Payum this way. The tag takes an optional
  `id` attribute for the name Payum should know it under, which is how a service is shared under the
  interface an action type-hints:

  ```yaml
  services:
      App\Payment\ExchangeRates:
          tags:
              - { name: payum.global_service, id: App\Payment\ExchangeRatesInterface }
  ```

* The `payum.action`, `payum.api` and `payum.extension` tags keep working and keep the same attributes.
  A tagged service now reaches a gateway as an entry of its container rather than as an `"@id"` string
  resolved at runtime, which means the `alias` attribute no longer names anything and is ignored.
* `Payum\Bundle\PayumBundle\ContainerAwareCoreGatewayFactory` and
  `Payum\Bundle\PayumBundle\Builder\CoreGatewayFactoryBuilder` are only used with payum/core 1.x.
  `Payum\Bundle\PayumBundle\PayumCoreGatewayFactory` takes their place, built by
  `Payum\Bundle\PayumBundle\Builder\PayumCoreGatewayFactoryBuilder`. Replacing
  `payum.core_gateway_factory_builder` with a factory of your own still works, it now has to build a
  factory implementing `Payum\Core\DI\ContainerConfiguration`.
* A gateway factory which does not implement `Payum\Core\DI\ContainerConfiguration` - which is still
  every gateway factory payum/core ships - is built the way it was in 1.x, and payum/core triggers a
  deprecation for it.

## 2.0 to 2.1

* `payum.http_client` service was removed. Use gateway's config to overwrite it.
* `payum.iso4217` service was removed. Use gateway's config to overwrite it.
* `payum.guzzle_client` service was removed. Use gateway's config to overwrite it.

## 1.x to 2.0

* The way gateways are configured was changed. The `gateways_v2` configuration option was renamed to `gateways`. The old `gateways` option was removed. Examples https://gist.github.com/makasim/f1aef97c8d1f2994456a

* The `GatewayFactory`, `CoreGatewayFactory`, `ContainerAwareRegistry` classes were removed.
* Factory `paypal_express_checkout_nvp` renamed to `paypal_express_checkout`
* Factory `paypal_pro_checkout_nvp` renamed to `paypal_pro_checkout`

## 1.2.0 to 1.2.1

* The service `payum.security.token_factory_internal` has been removed. Use `payum` service to get token factory.

## 0.15 to 1.0

* Php minimum version is 5.5
* Symfony minimum version is 2.7
* Service `payum.buzz.client` is no longer available. Use `payum.http_client` one.

## 0.14 to 0.15

* Everything that were Payment and PaymentXX were renamed to Gateway and GatewayXXX.
* Order was renamed to Payment.
* [config] `payments` section was renamed to `gateways`.

## 0.13 to 0.14

* `be2bill_onsite` payment was renamed to `be2bill_offsite`.
* `omnipay_onsite` payment was renamed to `omnipay_offsite`.
* `omnipay` payment was renamed to `omnipay_direct`.
* tag attribute `context` was renamed to `payment`.
* [config] `contexts` section was renamed to `payments`.
* [factory] New method `load` was added to `PaymentFactoryInterface`.

## 0.11 to 0.13

* `CreditCardType` was removed use one from bridge.
* `CreditCardExpirationDateType` was removed use one from bridge.
* `ObtainCreditCardAction` was removed use one from bridge.

## 0.10 to 0.11

* `InteractiveRequestListener` was renamed to `ReplyToHttpResponseListener`. The container service and related parameter was changed too. Now it takes replies and convert that to http response.
* The `Request` postfix was removed.

## 0.9 to 0.10

* `GetHttpQueryAction` was removed. Use `GetHttpRequestAction` from the bridge.
* `ResponseInteractiveRequest` was removed. Use one from the bridge. 

## 0.8 to 0.9

* Minimum Symfony 2.3 version required.
* Payment factory does not create action services any more. Instead, it uses actions defined in payment/foo.xml by tag.
* Payment factories configurations were simplified. Sub options `api.options` were moved to the root, section was removed.

    before:

    ```yml
    payum:
        a_context:
            a_factory:
                api:
                    options:
                        foo: foo
                        bar: bar
    ```

    after:

    ```yml
    payum:
        a_context:
            a_factory:
                foo: foo
                bar: bar
    ```

* `be2bill` payment factory does not provide support of onsite payments any more, use `be2bill_onsite` factory instead.
* [config] `storages` section inside a context was removed. Use new `storages` section in the root `payum` (by default storages are added to all payments). Here's how to migrate example:

    before:
 
    ```yml
    payum:
        a_context:
            a_factory:
                storages:
                    Acme\PaymentBundle\Entity\PaymentDetails:
                        doctrine:
                            driver: orm
    ```
    
    after: 
    
    ```yml
    payum:
        storages:
            Acme\PaymentBundle\Entity\PaymentDetails:
                payment:
                    contexts: [a_factory]
                doctrine: orm
                
        a_context:
            a_factory: 
    ```

* [factory] The signature of `StorageFactoryInterface::create` method was changed. Second `contextName` and fourth `paymentId` arguments were removed.
    

## 0.7 to 0.8

* `TokenFactory::createTokenForRoute` was renamed to `createToken`.

## 0.5 to 0.6

* AbstractPaymentFactory::addCommonExtensions method signature has been changed.
* AbstractPaymentFactory::addCommonActions method signature has been changed.
* `TokenManager` was removed. Its work was partially moved to `TokenFactory` and `HttpRequestVerifier`.
* `CaptureTokenizedDetailsRequest` was removed, use `Payum\Request\SecuredCaptureRequest` instead.
* `capture` url was changed if you still want use old one add `payum_deprecated_capture_do`.
* `notify` url was changed if you still want use old one add `payum_deprecated_notify_do`.
* `sync` url was changed if you still want use old one add `payum_deprecated_sync_do`.
* bundle configuration was changed. Now you have to configure `payum.security` section.

    before:

    ```yml
    payum:
        contexts:
            foo:
                storages:
                    Acme\PaymentBundle\Entity\TokenizedDetails:
                        filesystem:
                            storage_dir: %kernel.root_dir%/Resources/payments
                            id_property: token
    ```

    after:

    ```yml
    payum:
        security:
            token_storage:
                Acme\PaymentBundle\Entity\PayumSecurityToken:
                    filesystem:
                        storage_dir: %kernel.root_dir%/Resources/payments
                        id_property: hash
    ```

## 0.3 to 0.5

* Storage factory names has been changed. The `_storage` post fix removed. For example `doctrine_storage` now `doctrine`.
* Payment factory names has been changed. The `_payment` post fix removed. For example `omnipay_payment` now `omnipay`.
* `StorageFactoryInterface::create` method signature has been changed. Now it requires additional parameter `modelClass`. 
* Doctrine storage configuration does not have `model_class` option any more.
* Filesystem storage configuration does not have `model_class` option any more.
* `LazyContext` was removed in favor of `ContainerAwareRegistry`.
* `ContextInterface` was removed in favor of `ContainerAwareRegistry`.
* `ContextRegistry` was removed in favor of `ContainerAwareRegistry`.
* `payum` service now instance of `ContainerAwareRegistry` class. So the method `getContext` is not present any more.

## 0.2 to 0.3

* `capture_interactive_controller` option removed from config. Now `InteractiveRequestListener` does the job.
* `status_request_class` option was removed.
* `capture_finished_controller` option was removed.
* `ContextInterface::createStatusRequest` method was removed.
* `ContextInterface::getCaptureFinishedController` method was removed.
* `CaptureController` was removed. Use your own.

## 0.1 to 0.2

* The option `payum.context.a_context.xxx_payment.create_instruction_from_model_action` was removed. use `...actions` instead.
* `CaptureController::doCapture` method argument `modelId` was renamed to `model`. The route is also updated.
