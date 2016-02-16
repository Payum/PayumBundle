<?php
namespace Payum\Bundle\PayumBundle\Builder;

use Payum\Bundle\PayumBundle\CoreGatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @deprecated  since 1.2 and will be removed in 2.0 use one from bridge
 */
class CoreGatewayFactoryBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    private $actionTags;

    /**
     * @var array
     */
    private $extensionTags;

    /**
     * @var array
     */
    private $apiTags;

    /**
     * @param array $actionTags
     * @param array $extensionTags
     * @param array $apiTags
     * @param array $defaultConfig
     */
    public function __construct(array $actionTags, array $extensionTags, array $apiTags)
    {
        $this->actionTags = $actionTags;
        $this->extensionTags = $extensionTags;
        $this->apiTags = $apiTags;
    }

    /**
     * @param array $defaultConfig
     *
     * @return GatewayFactoryInterface
     */
    public function build(array $defaultConfig)
    {
        $coreGatewayFactory = new CoreGatewayFactory($this->actionTags, $this->extensionTags, $this->apiTags, $defaultConfig);
        $coreGatewayFactory->setContainer($this->container);

        return $coreGatewayFactory;
    }

    public function __invoke()
    {
        return call_user_func_array([$this, 'build'], func_get_args());
    }
}