<?php

namespace Payum\Bundle\PayumBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use Payum\Bundle\PayumBundle\EventListener\CancelGatewayConfigurationUpdateListener;
use Payum\Core\Model\GatewayConfigInterface;

final class CancelGatewayConfigurationUpdateListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldDoNothingIfEntityIsNotInstanceOfGatewayConfiguration()
    {
        $event = $this->prophesize(LifecycleEventArgs::class);
        $event->getEntity()->willReturn(new \stdClass());

        $listener = new CancelGatewayConfigurationUpdateListener();
        $listener->preUpdate($event->reveal());
    }

    /**
     * @test
     */
    public function shouldClearEntityChangeForGatewayConfiguration()
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $gatewayConfiguration = $this->prophesize(GatewayConfigInterface::class);
        $unityOfWork = $this->prophesize(UnitOfWork::class);
        $event = $this->prophesize(LifecycleEventArgs::class);
        $event->getEntity()->willReturn($gatewayConfiguration);
        $event->getEntityManager()->willReturn($entityManager);

        $entityManager->getUnitOfWork()->willReturn($unityOfWork);
        $unityOfWork->clearEntityChangeSet(spl_object_hash($gatewayConfiguration->reveal()))->shouldBeCalled();

        $listener = new CancelGatewayConfigurationUpdateListener();
        $listener->preUpdate($event->reveal());
    }
}
