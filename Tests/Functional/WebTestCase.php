<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public static function getKernelClass(): string
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return 'AppKernel';
    }
}
