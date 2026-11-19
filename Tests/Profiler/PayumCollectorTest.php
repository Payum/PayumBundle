<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\Profiler;

use Payum\Bundle\PayumBundle\Profiler\PayumCollector;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\Context;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PayumCollectorTest extends TestCase
{
    public function testShouldDrawTheTreeOfExecutedActions(): void
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $outer = new Context($gateway, new Capture(new Payment()), []);
        $inner = new Context($gateway, new Capture(new Payment()), [$outer]);
        $inner->setAction($this->createMock(ActionInterface::class));

        $collector = new PayumCollector(['gateways' => ['foo' => ['registered_by' => 'factory', 'factory' => 'offline']]]);
        $collector->onPreExecute($outer);
        $collector->onPreExecute($inner);
        $collector->collect(new Request(), new Response());

        $lines = explode(PHP_EOL, trim($collector->dump()));
        $this->assertSame('Capture[Payment]', $lines[0]);
        $this->assertStringContainsString('::execute(Capture[Payment])', $lines[1]);
        $this->assertCount(2, $collector->getActions());
        $this->assertSame([], $collector->getCommands());
        $this->assertSame('offline', $collector->getGateways()['foo']['factory']);

        $collector->reset();
        $this->assertSame('', $collector->dump());
    }
}
