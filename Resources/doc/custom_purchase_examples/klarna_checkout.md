# Klarna Checkout

Steps:

* [Download libraries](#download-libraries)
* [Configure gateway](#configure-gateway)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/get_it_started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ composer require "payum/klarna-checkout:@stable"
```

## Configure gateway

```yaml
#config/packages/payum.yml

payum:
    gateways:
        your_gateway_here:
            factory: klarna_checkout
            secret:  'get this from gateway side'
            merchant_id: 'REPLACE WITH YOUR MERCHANT_ID'
            sandbox: true
```

_**Attention**: You have to changed `your_gateway_name` to something more descriptive and domain related, for example `post_a_job_with_klarna`._

## Prepare payment

Now we are ready to prepare the payment. Here we set price, currency, cart items details and so on.
Please note that you have to set details in the payment gateway specific format.

```php
<?php
//src/Acme/PaymentBundle/Controller
namespace AcmeDemoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function preparePaypalExpressCheckoutPaymentAction(Payum $payum)
    {
        $paymentName = 'your_gateway_name';

        $storage = $payum->getStorage('Acme\PaymentBundle\Entity\PaymentDetails');

        /** @var \Acme\PaymentBundle\Entity\PaymentDetails $details */
        $details = $storage->create();
        $details['purchase_country'] = 'SE';
        $details['purchase_currency'] = 'SEK';
        $details['locale'] = 'sv-se';
        $storage->update($details);

        $captureToken = $payum->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $details,
            'acme_payment_done'
        );

        $details['merchant'] = array(
            'terms_uri' => 'https://example.com/terms',
            'checkout_uri' => 'https://example.com/fuck',
            'confirmation_uri' => $captureToken->getTargetUrl(),
            'push_uri' => $payum->getTokenFactory()->createNotifyToken($gatewayName, $details)->getTargetUrl()
        );
        $details['cart'] = array(
            'items' => array(
                array(
                   'reference' => '123456789',
                   'name' => 'Klarna t-shirt',
                   'quantity' => 2,
                   'unit_price' => 12300,
                   'discount_rate' => 1000,
                   'tax_rate' => 2500
                ),
                array(
                   'type' => 'shipping_fee',
                   'reference' => 'SHIPPING',
                   'name' => 'Shipping Fee',
                   'quantity' => 1,
                   'unit_price' => 4900,
                   'tax_rate' => 2500
                )
            )
        );
        $storage->update($details);

        return $this->redirect($captureToken->getTargetUrl());
    }
}
```

That's it. After the payment done you will be redirect to `acme_payment_done` action.
Check [this chapter](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md) to find out how this done action could look like.

## Next Step

* [Purchase done action](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md).
* [Configuration reference](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/configuration_reference.md).
* [Examples list](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/custom_purchase_examples.md).
* [Back to index](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md).
