<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Command;

use Payum\Bundle\PayumBundle\Command\DebugGatewayCommand;
use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DebugGatewayCommandTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldOutputDebugInfoAboutSingleGateway(): void
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $output = $this->executeConsole(new DebugGatewayCommand($payum), array(
            'gateway-name' => 'fooGateway',
        ));

        $this->assertStringContainsString('Found 1 gateways', $output);
        $this->assertStringContainsString('fooGateway (Payum\Core\Gateway):', $output);
        $this->assertStringContainsString('Actions:', $output);
        $this->assertStringContainsString('Extensions:', $output);
        $this->assertStringContainsString('Apis:', $output);

        $this->assertStringContainsString('Payum\Offline\Action\CaptureAction', $output);

        $this->assertStringContainsString('Payum\Core\Extension\StorageExtension', $output);
        $this->assertStringContainsString('Payum\Core\Storage\FilesystemStorage', $output);
        $this->assertStringContainsString('Payum\Core\Model\ArrayObject', $output);
    }

    /**
     * @test
     */
    public function shouldOutputDebugInfoAboutAllGateways(): void
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $output = $this->executeConsole(new DebugGatewayCommand($payum));

        $this->assertStringContainsString('Found 2 gateways', $output);
        $this->assertStringContainsString('fooGateway (Payum\Core\Gateway):', $output);
        $this->assertStringContainsString('barGateway (Payum\Core\Gateway):', $output);
    }

    /**
     * @test
     */
    public function shouldOutputInfoWhatActionsSupports(): void
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $output = $this->executeConsole(new DebugGatewayCommand($payum), array(
            'gateway-name' => 'fooGateway',
            '--show-supports' => true,
        ));

        $this->assertStringContainsString('Found 1 gateways', $output);
        $this->assertStringContainsString('fooGateway (Payum\Core\Gateway):', $output);
        $this->assertStringContainsString('Payum\Offline\Action\CaptureAction', $output);
        $this->assertStringContainsString('$request instanceof Capture &&', $output);
        $this->assertStringContainsString('$request->getModel() instanceof PaymentInterface', $output);
    }

    /**
     * @test
     */
    public function shouldOutputChoiceListGatewaysForNameGiven(): void
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $command = new DebugGatewayCommand($payum);
        $command->setApplication(new Application($this->client->getKernel()));

        $output = $this->executeConsole($command, [
            'gateway-name' => 'foo',
        ], ['0']);

        $this->assertStringContainsString('Choose a number for more information on the payum gateway', $output);
        $this->assertStringContainsString('[0] fooGateway', $output);
    }

    protected function executeConsole(Command $command, array $arguments = [], array $inputs = []): string
    {
        if (!$command->getApplication()) {
            $command->setApplication(new Application($this->client->getKernel()));
        }

        $arguments = array_replace(array(
            '--env' => 'test',
            'command' => $command->getName(),
        ), $arguments);

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);

        $commandTester->execute($arguments);

        return $commandTester->getDisplay();
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $input);
        rewind($stream);

        return $stream;
    }
}
