<?php

namespace Payum\Bundle\PayumBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Payum\Core\Model\GatewayConfigInterface;

final class CancelGatewayConfigurationUpdateListener
{
    /**
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof GatewayConfigInterface) {
            $unityOfWork = $event->getEntityManager()->getUnitOfWork();
            $unityOfWork->clearEntityChangeSet(spl_object_hash($event->getEntity()));
        }
    }
}
