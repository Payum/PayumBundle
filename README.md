<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become our client](https://forma-pro.com/)

---

# PayumBundle 
[![Gitter](https://badges.gitter.im/Payum/Payum.svg)](https://gitter.im/Payum/Payum?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![Build Status](https://github.com/Payum/PayumBundle/actions/workflows/tests.yaml/badge.svg)](https://github.com/Payum/PayumBundle/actions/workflows/tests.yaml)
[![Total Downloads](https://poser.pugx.org/payum/payum-bundle/d/total.png)](https://packagist.org/packages/payum/payum-bundle) 
[![Latest Stable Version](https://poser.pugx.org/payum/payum-bundle/version.png)](https://packagist.org/packages/payum/payum-bundle)

The bundle  integrate [payum](https://github.com/Payum/Payum) into [symfony](https://symfony.com/) framework.
It already supports [+35 gateways](https://github.com/Payum/Payum/blob/master/docs/supported-gateways.md).
Provide nice configuration layer, secured capture controller, storages integration and lots of more features.

[Sylius, an open source headless eCommerce platform](https://sylius.com/), base its payment solutions on top of the bundle.

## Resources

* [Site](https://payum.forma-pro.com/)
* [Documentation](https://payum.gitbook.io/payum/symfony/get-it-started)
* [Sandbox](https://github.com/makasim/PayumBundleSandbox)
* [Questions](https://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/PayumBundle/issues)
* [Twitter](https://twitter.com/payumphp)

## Examples

### Configure:

```yaml
payum:
    storages:
        Payum\Core\Model\Payment:
            filesystem:
                storage_dir: '%kernel.root_dir%/Resources/payments'
                id_property: number

    security:
        token_storage:
            Payum\Core\Model\Token:
                filesystem:
                    storage_dir: '%kernel.root_dir%/Resources/gateways'
                    id_property: hash
                
    gateways:
        offline:
            factory: offline
```

_note_ if you're using Symfony 4+ then create `config/packages/payum.yaml` file with contents described above.

### Purchase

```php
<?php
use Payum\Core\Model\Payment;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;

$payment = new Payment;
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(123); // 1.23 EUR
$payment->setDescription('A description');
$payment->setClientId('anId');
$payment->setClientEmail('foo@example.com');

$gateway = $this->get('payum')->getGateway('offline');
$gateway->execute(new Capture($payment));
```

### Get status

```php
<?php
use Payum\Core\Request\GetHumanStatus;

$gateway->execute($status = new GetHumanStatus($payment));

echo $status->getValue();
```

### Other operations.

```php
<?php
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Refund;

$gateway->execute(new Authorize($payment));

$gateway->execute(new Refund($payment));

$gateway->execute(new Cancel($payment));
```

## Contributing

PayumBundle is an open source, community-driven project. Pull requests are very welcome.

## Like it? Spread the word!

Star PayumBundle on [GitHub](https://github.com/Payum/PayumBundle) or [packagist](https://packagist.org/packages/payum/payum-bundle).

## License

The bundle is released under the [MIT License](Resources/meta/LICENSE).
