<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\Functional\Fixture;

use Payum\Core\Action\ActionInterface;

class TaggedCaptureAction implements ActionInterface
{
    public function execute($request): void
    {
    }

    public function supports($request): bool
    {
        return false;
    }
}
