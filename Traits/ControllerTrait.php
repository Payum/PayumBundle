<?php
declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Traits;

use Payum\Core\Payum;
use function array_merge;

trait ControllerTrait
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'payum' => Payum::class,
        ]);
    }
}
