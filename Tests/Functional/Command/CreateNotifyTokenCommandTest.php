<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Command;

use Payum\Bundle\PayumBundle\Command\CreateNotifyTokenCommand;
use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CreateNotifyTokenCommandTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldCreateNotifyTokenWithoutModel(): void
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $output = $this->executeConsole(new CreateNotifyTokenCommand($payum), array(
            'gateway-name' => 'fooGateway'
        ));

        $this->assertStringContainsString('Hash: ', $output);
        $this->assertStringContainsString('Url: ', $output);
        $this->assertStringContainsString('Details: null', $output);
    }

    /**
     * @test
     */
    public function shouldCreateNotifyTokenWithModel(): void
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $modelClass = 'Payum\Core\Model\ArrayObject';

        $storage = $payum->getStorage($modelClass);
        $model = $storage->create();
        $storage->update($model);

        $modelId = $storage->identify($model)->getId();

        $output = $this->executeConsole(new CreateNotifyTokenCommand($payum), array(
            'gateway-name' => 'fooGateway',
            '--model-class' => $modelClass,
            '--model-id' => $modelId
        ));

        $this->assertStringContainsString('Hash: ', $output);
        $this->assertStringContainsString('Url: ', $output);
        $this->assertStringContainsString("Details: $modelClass#$modelId", $output);
    }

    /**
     * @param string[] $arguments
     */
    protected function executeConsole(Command $command, array $arguments = array()): string
    {
        $command->setApplication(new Application($this->client->getKernel()));

        $arguments = array_replace(array(
            '--env' => 'test',
            'command' => $command->getName()
        ), $arguments);

        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments);

        return $commandTester->getDisplay();
    }
}
