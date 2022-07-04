# Custom api usage

Sometime you asked to store payment gateway credentials to database. 
If this is your case read [Configure payment in backend](configure-payment-in-backend.md) chapter.
Here we would describe how you can add an api defined as service.

## Api factory

First, we have to create an api factory.
The factory would create the desired api using database or what ever else you want.

```php
<?php
// src/Acme/PaymentBundle/Payum/Api/Factory.php;
namespace Acme\PaymentBundle\Payum\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Api;

class Factory
{
    private string $username;
    private string $password;
    private string $signature;
    
    public function __construct(string $username, string $password, string $signature)
    {
        $this->username = $username;
        $this->password = $password;
        $this->signature = $signature;
    }

    /**
     * @return Api
     */
    public function createPaypalExpressCheckoutApi()
    {
        return new Api(array(
            'username' => $this->username,
            'password' => $this->password,
            'signature' => $this->signature,
            'sandbox' => true
        ));
    }
}
```

As you could see we use container to build paypal api.
Feel free to change it to suit your needs.
Now we have to create an api service which is created by the factory one:

```yaml
# src/Acme/PaymentBundle/Resources/config/services.yml

services:

    # ...

    acme.payment.payum.api.factory:
        class: Acme\PaymentBundle\Payum\Api\Factory
        arguments:
            $username: '%env(PAYPAL_EXPRESS_CHECKOUT_USERNAME)%'
            $password: '%env(PAYPAL_EXPRESS_CHECKOUT_PASSWORD)%'
            $signature: '%env(PAYPAL_EXPRESS_CHECKOUT_SIGNATURE)%'

    acme.payment.payum.paypal_express_checkout_api:
        class: Payum\Paypal\ExpressCheckout\Nvp\Api
        public: true
        factory_service: acme.payment.payum.api.factory
        factory_method: createPaypalExpressCheckoutApi
```

When we are done we can tell payum to use this service instead of default one:

```yaml
# config/packages/payum.yml

payum:
    gateways:
        your_gateway_name_here:
            factory: paypal_express_checkout
            username:  NOT USED
            password:  NOT USED
            signature: NOT USED
            sandbox: true
            payum.api: @acme.payment.payum.paypal_express_checkout_api

```

That's it!

* [Custom purchase examples](custom_purchase_examples.md).
* [Configure payment in backend](configure-payment-in-backend.md)
* [Done action](purchase_done_action.md)
* [Sandbox](sandbox.md)
* [Console commands](console_commands.md)
* [Debugging](debugging.md)
* [Container tags](container_tags.md).
* [Payment configurations](configuration_reference.md)
* [Back to index](index.md).
