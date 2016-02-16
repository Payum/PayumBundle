<?php
namespace Payum\Bundle\PayumBundle;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesBuilderPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoriesPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewaysPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildStoragesPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\CustomStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel1StorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel2StorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PayumBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension PayumExtension */
        $extension = $container->getExtension('payum');

        $extension->addStorageFactory(new FilesystemStorageFactory);
        $extension->addStorageFactory(new DoctrineStorageFactory);
        $extension->addStorageFactory(new CustomStorageFactory);
        $extension->addStorageFactory(new Propel1StorageFactory);
        $extension->addStorageFactory(new Propel2StorageFactory);

        $container->addCompilerPass(new BuildConfigsPass());
        $container->addCompilerPass(new BuildGatewaysPass);
        $container->addCompilerPass(new BuildStoragesPass);
        $container->addCompilerPass(new BuildGatewayFactoriesPass);
        $container->addCompilerPass(new BuildGatewayFactoriesBuilderPass());
    }
}
