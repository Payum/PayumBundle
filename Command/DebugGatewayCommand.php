<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Storage\AbstractStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(name: 'debug:payum:gateway', aliases: ['payum:gateway:debug'])]
class DebugGatewayCommand extends Command
{
    public function __construct(protected Payum $payum)
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->addArgument('gateway-name', InputArgument::OPTIONAL, 'The gateway name you want to get information about.')
            ->addOption('show-supports', null, InputOption::VALUE_NONE, 'Show what actions supports.')
        ;
    }

    /**
     * {@inheritDoc}
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $gateways = $this->payum->getGateways();

        if ($gatewayName = $input->getArgument('gateway-name')) {
            $gatewayName = $this->findProperGatewayName($input, $output, $gateways, $gatewayName);
            $gateways = array(
                $gatewayName => $this->payum->getGateway($gatewayName),
            );
        }

        $output->writeln('<info>Order of actions, apis, extensions matters</info>');

        $output->writeln(sprintf('Found <info>%d</info> gateways', count($gateways)));

        foreach ($gateways as $name => $gateway) {
            $output->writeln('');
            $output->writeln(sprintf('%s (%s):', $name, get_class($gateway)));

            if (!$gateway instanceof Gateway) {
                continue;
            }

            $rp = new \ReflectionProperty($gateway, 'actions');
            $rp->setAccessible(true);
            $actions = $rp->getValue($gateway);
            $rp->setAccessible(false);

            $output->writeln("\t<info>Actions:</info>");
            foreach ($actions as $action) {
                $output->writeln(sprintf("\t%s", get_class($action)));

                if ($input->getOption('show-supports')) {
                    $rm = new \ReflectionMethod($action, 'supports');
                    $output->write("\n\t".implode("\n\t", $this->getMethodCode($rm)));
                }
            }

            $rp = new \ReflectionProperty($gateway, 'extensions');
            $rp->setAccessible(true);
            $collection = $rp->getValue($gateway);
            $rp->setAccessible(false);

            $rp = new \ReflectionProperty($collection, 'extensions');
            $rp->setAccessible(true);
            $extensions = $rp->getValue($collection);
            $rp->setAccessible(false);

            $output->writeln("");
            $output->writeln("\t<info>Extensions:</info>");
            foreach ($extensions as $extension) {
                $output->writeln(sprintf("\t%s", get_class($extension)));

                if ($extension instanceof StorageExtension) {
                    $rp = new \ReflectionProperty($extension, 'storage');
                    $rp->setAccessible(true);
                    $storage = $rp->getValue($extension);
                    $rp->setAccessible(false);

                    $output->writeln(sprintf("\t\t<info>Storage</info>: %s", get_class($storage)));

                    if ($storage instanceof AbstractStorage) {
                        $rp = new \ReflectionProperty($storage, 'modelClass');
                        $rp->setAccessible(true);
                        $modelClass = $rp->getValue($storage);
                        $rp->setAccessible(false);

                        $output->writeln(sprintf("\t\t<info>Model</info>: %s", $modelClass));
                    }
                }
            }

            $rp = new \ReflectionProperty($gateway, 'apis');
            $rp->setAccessible(true);
            $apis = $rp->getValue($gateway);
            $rp->setAccessible(false);

            $output->writeln("");
            $output->writeln("\t<info>Apis:</info>");
            foreach ($apis as $api) {
                $output->writeln(sprintf("\t%s", get_class($api)));
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @return list<string>
     */
    protected function getMethodCode(\ReflectionMethod $reflectionMethod): array
    {
        $file = file($reflectionMethod->getFileName());

        $methodCodeLines = array();
        foreach (range($reflectionMethod->getStartLine(), $reflectionMethod->getEndLine() - 1) as $line) {
            $methodCodeLines[] = $file[$line];
        }

        return array_values($methodCodeLines);
    }

    /**
     * @param array<string, GatewayInterface> $gateways
     */
    private function findProperGatewayName(InputInterface $input, OutputInterface $output, array $gateways, string $name): string
    {
        $helperSet = $this->getHelperSet();
        if (!$helperSet->has('question') || isset($gateways[$name]) || !$input->isInteractive()) {
            return $name;
        }

        $matchingGateways = $this->findGatewaysContaining($gateways, $name);
        if (empty($matchingGateways)) {
            throw new \InvalidArgumentException(sprintf('No Payum gateways found that match "%s".', $name));
        }
        $question = new ChoiceQuestion('Choose a number for more information on the payum gateway', $matchingGateways);
        $question->setErrorMessage('Payum gateway %s is invalid.');

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    /**
     * @param array<string, GatewayInterface> $gateways
     * @return list<string>
     */
    private function findGatewaysContaining(array $gateways, string $name): array
    {
        $threshold = 1e3;
        $foundGateways = array();

        foreach ($gateways as $gatewayName => $gateway) {
            $lev = levenshtein($name, $gatewayName);
            if ($lev <= strlen($name) / 3 || str_contains($gatewayName, $name)) {
                $foundGateways[$gatewayName] = isset($foundGateways[$gatewayName]) ? $foundGateways[$gateway] - $lev : $lev;
            }
        }

        $foundGateways = array_filter($foundGateways, function ($lev) use ($threshold) { return $lev < 2*$threshold; });
        asort($foundGateways);

        return array_keys($foundGateways);
    }
}
