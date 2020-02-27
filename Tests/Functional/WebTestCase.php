<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $client;

    /**
     * @var ContainerInterface
     */
    protected static $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        static::$container = static::$kernel->getContainer();
    }

    /**
     * @return string
     */
    public static function getKernelClass()
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return 'AppKernel';
    }
}
