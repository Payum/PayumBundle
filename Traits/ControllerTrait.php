<?php
declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Traits;

use Payum\Core\Payum;
use Symfony\Component\HttpKernel\Kernel;
use function array_merge;

if (Kernel::MAJOR_VERSION >= 6 ) {
    trait ControllerTrait
    {
        public static function getSubscribedServices(): array
        {
            return array_merge(parent::getSubscribedServices(), [
                'payum' => Payum::class,
            ]);
        }
    }
} else {
    trait ControllerTrait
    {
        public static function getSubscribedServices()
        {
            return array_merge(parent::getSubscribedServices(), [
                'payum' => Payum::class,
            ]);
        }
    }
}
