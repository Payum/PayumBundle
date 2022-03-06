<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class PayumController extends AbstractController
{
    public function __construct(protected ?Payum $payum)
    {}

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'payum' => Payum::class,
        ]);
    }
}
