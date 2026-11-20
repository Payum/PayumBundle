<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Registry\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class DebugGatewayFactoryCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('debug:payum:gateway-factory')
            ->setAliases(array(
                'payum:gateway-factory:debug',
            ))
            ->addArgument('factory-name', InputArgument::OPTIONAL, 'The gateway factory name you want to get information about.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factories = $this->getPayum()->getGatewayFactories();

        if ($factoryName = $input->getArgument('factory-name')) {
            $output->writeln('<info>Order of actions, apis, extensions matters</info>');

            $factoryName = $this->findProperGatewayFactoryName($input, $output, $factories, $factoryName);
            $factory = $this->getPayum()->getGatewayFactory($factoryName);
            $output->writeln($factoryName.': '.get_class($factory));
            $output->writeln('');

            $table = new Table($output);
            $table
                ->setHeaders(['Name', 'Value'])
                ->setStyle('compact')
            ;

            foreach ($factory->createConfig() as $name => $value) {
                if (is_object($value)) {
                    $table->addRow([$name, 'Object(' . get_class($value) . ')']);
                } elseif ('payum.required_options' == $name && is_array($value)) {
                    $table->addRow([$name, implode(', ', $value)]);
                } elseif ('payum.default_options' == $name && is_array($value)) {
                    $table->addRow([$name, implode(', ', array_keys($value))]);
                } elseif (is_array($value) && empty($value)) {
                    $table->addRow([$name, '[]']);
                } elseif (is_array($value)) {


                    $table->addRow([$name, '']);

                    foreach ($value as $subName => $subValue) {
                        $table->addRow(['   '.$subName, wordwrap($subValue, 70, "\n", true)]);
                    }

                    continue;
                } else {
                    $table->addRow([$name, $value]);
                }

            }

            $table->render();
        } else {


            $output->writeln(sprintf('Found <info>%d</info> gateway factories', count($factories)));
            $output->writeln('');

            $table = new Table($output);
            $table
                ->setHeaders(['Name', 'Class'])
                ->setStyle('compact')
            ;

            foreach ($factories as $name => $factory) {
                $table->addRow([$name, get_class($factory)]);
            }

            $table->render();
        }
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->getContainer()->get('payum');
    }

    private function findProperGatewayFactoryName(InputInterface $input, OutputInterface $output, $factories, $name)
    {
        $helperSet = $this->getHelperSet();
        if (!$helperSet->has('question') || isset($factories[$name]) || !$input->isInteractive()) {
            return $name;
        }

        $matchingGatewayFactory = $this->findGatewayFactoriesContaining($factories, $name);
        if (empty($matchingGatewayFactory)) {
            throw new \InvalidArgumentException(sprintf('No Payum gateway factory found that match "%s".', $name));
        }
        $question = new ChoiceQuestion('Choose a number for more information on the payum gateway factory', $matchingGatewayFactory);
        $question->setErrorMessage('Payum gateway factory %s is invalid.');

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function findGatewayFactoriesContaining($factories, $name)
    {
        $threshold = 1e3;
        $foundGateways = array();

        foreach ($factories as $factoryName => $factory) {
            $lev = levenshtein($name, $factoryName);
            if ($lev <= strlen($name) / 3 || false !== strpos($factoryName, $name)) {
                $foundGateways[$factoryName] = isset($foundGateways[$factoryName]) ? $foundGateways[$factory] - $lev : $lev;
            }
        }

        $foundGateways = array_filter($foundGateways, function ($lev) use ($threshold) { return $lev < 2*$threshold; });
        asort($foundGateways);

        return array_keys($foundGateways);
    }
}
