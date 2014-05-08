<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Command;

use Payum\Bundle\PayumBundle\Command\CreateNotifyTokenCommand;
use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CreateNotifyTokenCommandTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @test
     */
    public function shouldCreateNotifyTokenWithoutModel()
    {
        $output = $this->executeConsole(new CreateNotifyTokenCommand, array(
            'payment-name' => 'offline'
        ));

        $this->assertContains('Hash: ', $output);
        $this->assertContains('Url: ', $output);
        $this->assertContains('Details: null', $output);
    }

    /**
     * @test
     */
    public function shouldCreateNotifyTokenWithModel()
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $modelClass = 'Payum\Core\Model\ArrayObject';

        $storage = $payum->getStorageForClass($modelClass, 'offline');
        $model = $storage->createModel();
        $storage->updateModel($model);

        $modelId = $storage->getIdentificator($model)->getId();

        $output = $this->executeConsole(new CreateNotifyTokenCommand, array(
            'payment-name' => 'offline',
            '--model-class' => $modelClass,
            '--model-id' => $modelId
        ));

        $this->assertContains('Hash: ', $output);
        $this->assertContains('Url: ', $output);
        $this->assertContains("Details: $modelClass#$modelId", $output);
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @param string[]                                   $arguments
     * @param string[]                                   $options
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = array(), array $options = array())
    {
        $command->setApplication(new Application($this->client->getKernel()));
        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->client->getContainer());
        }

        $arguments = array_replace(array('command' => $command->getName()), $arguments);
        $options = array_replace(array('--env' => 'test'), $options);

        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments, $options);

        return $commandTester->getDisplay();
    }
}
