<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Command;

use Payum\Bundle\PayumBundle\Command\DebugGatewayCommand;
use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class DebugGatewayCommandTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldOutputDebugInfoAboutSingleGateway()
    {
        $output = $this->executeConsole(new DebugGatewayCommand(), array(
            'gateway-name' => 'fooGateway',
        ));

        $this->assertContains('Found 1 gateways', $output);
        $this->assertContains('fooGateway (Payum\Core\Gateway):', $output);
        $this->assertContains('Actions:', $output);
        $this->assertContains('Extensions:', $output);
        $this->assertContains('Apis:', $output);

        $this->assertContains('Payum\Offline\Action\CaptureAction', $output);

        $this->assertContains('Payum\Core\Extension\StorageExtension', $output);
        $this->assertContains('Payum\Core\Storage\FilesystemStorage', $output);
        $this->assertContains('Payum\Core\Model\ArrayObject', $output);
    }

    /**
     * @test
     */
    public function shouldOutputDebugInfoAboutAllGateways()
    {
        $output = $this->executeConsole(new DebugGatewayCommand());

        $this->assertContains('Found 2 gateways', $output);
        $this->assertContains('fooGateway (Payum\Core\Gateway):', $output);
        $this->assertContains('barGateway (Payum\Core\Gateway):', $output);
    }

    /**
     * @test
     */
    public function shouldOutputInfoWhatActionsSupports()
    {
        $output = $this->executeConsole(new DebugGatewayCommand(), array(
            'gateway-name' => 'fooGateway',
            '--show-supports' => true,
        ));

        $this->assertContains('Found 1 gateways', $output);
        $this->assertContains('fooGateway (Payum\Core\Gateway):', $output);
        $this->assertContains('Payum\Offline\Action\CaptureAction', $output);
        $this->assertContains('$request instanceof Capture &&', $output);
        $this->assertContains('$request->getModel() instanceof PaymentInterface', $output);
    }

    /**
     * @test
     */
    public function shouldOutputChoiceListGatewaysForNameGiven()
    {
        $command = new DebugGatewayCommand();
        $command->setApplication(new Application($this->client->getKernel()));

        $output = $this->executeConsole($command, [
            'gateway-name' => 'foo',
        ], ['0']);

        $this->assertContains('Choose a number for more information on the payum gateway', $output);
        $this->assertContains('[0] fooGateway', $output);
    }

    /**
     * @param Command $command
     * @param string[] $arguments
     * @param string[] $inputs
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = [], array $inputs = [])
    {
        if (!$command->getApplication()) {
            $command->setApplication(new Application($this->client->getKernel()));
        }

        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->client->getContainer());
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
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
