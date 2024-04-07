<?php

namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait ContainerAwareTrait {

  protected ?ContainerInterface $container;

  public function setContainer(?ContainerInterface $container): void {
    $this->container = $container;
  }

}
