<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function get_parent_class;
use function method_exists;

abstract class WebTestCase extends BaseWebTestCase
{
    protected KernelBrowser $client;

    /**
     * @var $container ContainerInterface
     * @deprecated since version 2.5. use getContainer() instead
     */
    protected static $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        if (method_exists(get_parent_class(self::class), 'getContainer')) {
            static::$container = parent::getContainer();
        } else {
            static::$container = static::$kernel->getContainer();
        }
    }

    protected static function getContainer(): Container
    {
        if (method_exists(get_parent_class(self::class), 'getContainer')) {
            return parent::getContainer();
        }

        return self::$container;
    }

    public static function getKernelClass(): string
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return 'AppKernel';
    }
}
