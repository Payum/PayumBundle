How payment done action could look like?
========================================

We assume you already know how to prepare payment details and how to capture them.
The last thing in this store what to do after?
This chapter should cover these questions.

Well, let's assume you created capture token this way while preparing payment:

```php
<?php
$captureToken = $this->get('payum.token_manager')->createTokenForCaptureRoute(
    $paymentName,
    $paymentDetails,
    'acme_payment_done'
);
```

Pay attention to third parameter `acme_payment_done`.
It is the route of url you will be redirected after capture done its job. Let's look at an example of how this action may look like:

```php
    public function captureDoneAction(Request $request)
    {
        $token = $this->get('payum.token_manager')->getTokenFromRequest($request);

        $payment = $this->get('payum')->getPayment($token->getPaymentName());

        $status = new BinaryMaskStatusRequest($token);
        $payment->execute($status);

        if ($status->isSuccess()) {
            $this->getUser()->addCredits(100);
            $this->getRequest()->getSession()->getFlashBag()->set(
                'notice',
                'Payment success. Credits were added'
            );
        } else if ($status->isPending()) {
            $this->getRequest()->getSession()->getFlashBag()->set(
                'notice',
                'Payment is still pending. Credits were not added'
            );
        } else {
            $this->getRequest()->getSession()->getFlashBag()->set('error', 'Payment failed');
        }

        return $this->redirect('homepage');
    }
```

In general you have to check status of the payment and do whatever you want depending on it.
For example if payment success you  would add a user some credits or update expiration date.
If not you redirect him to homepage and show a flash message with a bad news.

### Next Step

* [Back to index](index.md).