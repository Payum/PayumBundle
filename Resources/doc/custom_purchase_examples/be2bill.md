# Be2bill credit card

Steps:

* [Download libraries](#download-libraries)
* [Configure payment](#configure-gateway)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/get_it_started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ composer require "payum/be2bill"
```

## Configure gateway

```yaml
#config/packages/payum.yml

payum:
    gateways:
        your_gateway_here:
            factory: be2bill
            identifier: 'get this from gateway'
            password: 'get this from gateway'
            sandbox: true
```

_**Attention**: You have to changed `your_gateway_name` to something more descriptive and domain related, for example `post_a_job_with_be2bill`._

## Prepare payment

Now we are ready to prepare the payment. Here we set price, currency, cart items details and so.
Please note that you have to set details in the payment gateway specific format.

```php
<?php
//src/Acme/PaymentBundle/Controller
namespace AcmeDemoBundle\Controller;

use Acme\PaymentBundle\Entity\PaymentDetails;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function prepareBe2BillPaymentAction(Request $request, Payum $payum)
    {
        $gatewayName = 'your_gateway_name';

        $storage = $payum->getStorage('Acme\PaymentBundle\Entity\PaymentDetails');

        /** @var \Acme\PaymentBundle\Entity\PaymentDetails */
        $details = $storage->create();
        //be2bill amount format is cents: for example:  100.05 (EUR). will be 10005.
        $details['AMOUNT'] = 10005;
        $details['CLIENTEMAIL'] = 'user@email.com';
        $details['CLIENTUSERAGENT'] = $request->headers->get('User-Agent', 'Unknown');
        $details['CLIENTIP'] = $request->getClientIp();
        $details['CLIENTIDENT'] = 'payerId';
        $details['DESCRIPTION'] = 'Payment for digital stuff';
        $details['ORDERID'] = 'orderId';
        $storage->update($details);

        $captureToken = $payum->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $details,
            'acme_payment_done' // the route to redirect after capture;
        );

        return $this->redirect($captureToken->getTargetUrl());
    }
}
```

That's it. It will ask user for credit card and convert it to payment specific format. After the payment done you will be redirect to `acme_payment_done` action.
Check [this chapter](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md) to find out how this done action could look like.

If you still able to pass credit card details explicitly: 
  
```php
<?php
use Payum\Core\Security\SensitiveValue;

$details['CARDCODE'] = new SensitiveValue('5555 5567 7825 0000');
$details['CARDCVV'] = new SensitiveValue(123);
$details['CARDFULLNAME'] = new SensitiveValue('John Doe');
$details['CARDVALIDITYDATE'] = new SensitiveValue('15-11');
```

## Next Step

* [Purchase done action](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md).
* [Configuration reference](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/configuration_reference.md).
* [Examples list](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/custom_purchase_examples.md).
* [Back to index](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md).
