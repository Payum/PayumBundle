<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayConfigsPass;
use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Core\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BuildGatewayConfigsPassTest extends TestCase
{
    protected function setUp(): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('Needs payum/core 2.0 or later.');
        }
    }

    public function testShouldRegisterEachTagAsAGateway(): void
    {
        $service = new Definition();
        $service->addTag('payum.gateway_config', ['gateway' => 'stripe_eu']);
        $service->addTag('payum.gateway_config', ['gateway' => 'stripe_us']);

        $container = $this->containerWith($service);

        (new BuildGatewayConfigsPass())->process($container);

        $this->assertEquals(
            [
                ['registerGateway', ['stripe_eu', new Reference('aservice')]],
                ['registerGateway', ['stripe_us', new Reference('aservice')]],
            ],
            $container->getDefinition('payum.builder')->getMethodCalls()
        );
    }

    public function testThrowIfTagMissesGatewayAttribute(): void
    {
        $service = new Definition();
        $service->addTag('payum.gateway_config');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The payum.gateway_config tag on service "aservice" requires a gateway attribute.');

        (new BuildGatewayConfigsPass())->process($this->containerWith($service));
    }

    public function testThrowIfTheGatewayIsAlreadyRegistered(): void
    {
        $service = new Definition();
        $service->addTag('payum.gateway_config', ['gateway' => 'offline']);

        $container = $this->containerWith($service);
        $container->getDefinition('payum.builder')->addMethodCall('addGateway', ['offline', ['factory' => 'offline']]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The gateway "offline" is registered twice');

        (new BuildGatewayConfigsPass())->process($container);
    }

    private function containerWith(Definition $service): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition('payum.builder', new Definition());
        $container->setDefinition('aservice', $service);

        return $container;
    }
}
