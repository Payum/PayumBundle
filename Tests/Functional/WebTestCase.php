<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class WebTestCase extends BaseWebTestCase
{
    protected KernelBrowser $client;

    /**
     * @var $container ContainerInterface
     */
    protected static $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        static::$container = static::$kernel->getContainer();
    }

    public static function getKernelClass(): string
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return 'AppKernel';
    }
}
