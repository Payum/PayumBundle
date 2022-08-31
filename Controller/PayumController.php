<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Bundle\PayumBundle\Traits\ControllerTrait;
use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class PayumController extends AbstractController
{
    use ControllerTrait;

    public function __construct(protected Payum $payum)
    {}
}
