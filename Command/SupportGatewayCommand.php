<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Model\Payment;
use Payum\Core\Model\Payout as PayoutModel;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Support;
use Payum\Core\Request\Sync;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SupportGatewayCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('payum:gateway:supports')
            ->addArgument('gateway-name', InputArgument::REQUIRED, 'The gateway name you want to get information about.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requestClasses = [
            Capture::class,
            Authorize::class,
            Refund::class,
            Cancel::class,
            Notify::class,
            Payout::class,
            Sync::class
        ];

        $gatewayName = $input->getArgument('gateway-name');
        $gatewayName = $this->findProperGatewayName($input, $output, $this->getPayum()->getGateways(), $gatewayName);
        $gateway = $this->getPayum()->getGateway($gatewayName);

        $table = new Table($output);
        $table
            ->setHeaders(['Action', 'ArrayAccess', 'Payment', 'Payout'])
            ->setStyle('compact')
        ;

        $render = function(Support $support) {
            return $support->isSupported() ? 'yes' : 'no';
        };

        foreach ($requestClasses as $requestClass) {
            $gateway->execute($supportArray = new Support(new $requestClass(new \ArrayObject())));
            $gateway->execute($supportPayment = new Support(new $requestClass(new Payment())));
            $gateway->execute($supportPayout = new Support(new $requestClass(new PayoutModel())));

            $table->addRow([
                (new \ReflectionClass($requestClass))->getShortName(),
                $render($supportArray),
                $render($supportPayment),
                $render($supportPayout)
            ]);
        }
        $table->render();
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->getContainer()->get('payum');
    }

    private function findProperGatewayName(InputInterface $input, OutputInterface $output, $gateways, $name)
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

    private function findGatewaysContaining($gateways, $name)
    {
        $threshold = 1e3;
        $foundGateways = array();

        foreach ($gateways as $gatewayName => $gateway) {
            $lev = levenshtein($name, $gatewayName);
            if ($lev <= strlen($name) / 3 || false !== strpos($gatewayName, $name)) {
                $foundGateways[$gatewayName] = isset($foundGateways[$gatewayName]) ? $foundGateways[$gateway] - $lev : $lev;
            }
        }

        $foundGateways = array_filter($foundGateways, function ($lev) use ($threshold) { return $lev < 2*$threshold; });
        asort($foundGateways);

        return array_keys($foundGateways);
    }
}
