<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Exception\RuntimeException;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payum:status', description: 'Allows to get a payment status.')]
class StatusCommand extends Command
{
    protected static $defaultName = 'payum:status';

    protected Payum $payum;

    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Allows to get a payment status.')
            ->addArgument('gateway-name', InputArgument::REQUIRED, 'The gateway name')
            ->addOption('model-class', null, InputOption::VALUE_REQUIRED, 'The model class')
            ->addOption('model-id', null, InputOption::VALUE_REQUIRED, 'The model id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $gatewayName = $input->getArgument('gateway-name');
        $modelClass = $input->getOption('model-class');
        $modelId = $input->getOption('model-id');

        $storage = $this->payum->getStorage($modelClass);
        if (false === $model = $storage->find($modelId)) {
            throw new RuntimeException(sprintf(
                'Cannot find model with class %s and id %s.',
                $modelClass,
                $modelId
            ));
        }

        $status = new GetHumanStatus($model);
        $this->payum->getGateway($gatewayName)->execute($status);

        $output->writeln(sprintf('Status: %s', $status->getValue()));

        return 0;
    }
}
