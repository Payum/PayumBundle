<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Exception\RuntimeException;
use Payum\Core\Payum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payum:security:create-capture-token')]
class CreateCaptureTokenCommand extends Command
{
    protected static $defaultName = 'payum:security:create-capture-token';

    private Payum $payum;

    public function __construct(Payum $payum)
    {
        $this->payum = $payum;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName)
            ->addArgument('gateway-name', InputArgument::REQUIRED, 'The gateway name associated with the token')
            ->addOption('model-class', null, InputOption::VALUE_OPTIONAL, 'The model class associated with the token')
            ->addOption('model-id', null, InputOption::VALUE_OPTIONAL, 'The model id associated with the token')
            ->addOption('after-url', null, InputOption::VALUE_REQUIRED, 'The url\route user will be redirected after the purchase is done.', null)
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $gatewayName = $input->getArgument('gateway-name');
        $modelClass = $input->getOption('model-class');
        $modelId = $input->getOption('model-id');
        $afterUrl = $input->getOption('after-url');

        $model = null;
        if ($modelClass && $modelId) {
            if (false === $model = $this->payum->getStorage($modelClass)->find($modelId)) {
                throw new RuntimeException(sprintf(
                    'Cannot find model with class %s and id %s.',
                    $modelClass,
                    $modelId
                ));
            }
        }

        $token = $this->payum->getTokenFactory()->createCaptureToken($gatewayName, $model, $afterUrl);

        $output->writeln(sprintf('Hash: <info>%s</info>', $token->getHash()));
        $output->writeln(sprintf('Url: <info>%s</info>', $token->getTargetUrl()));
        $output->writeln(sprintf('After Url: <info>%s</info>', $token->getAfterUrl() ?: 'null'));
        $output->writeln(sprintf('Details: <info>%s</info>', (string) $token->getDetails()));

        return 0;
    }
}
