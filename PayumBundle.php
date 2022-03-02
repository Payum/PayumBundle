<?php
namespace Payum\Bundle\PayumBundle;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesBuilderPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewaysPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildStoragesPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PayumBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new BuildConfigsPass());
        $container->addCompilerPass(new BuildGatewaysPass);
        $container->addCompilerPass(new BuildStoragesPass);
        $container->addCompilerPass(new BuildGatewayFactoriesPass);
        $container->addCompilerPass(new BuildGatewayFactoriesBuilderPass());
    }
}
