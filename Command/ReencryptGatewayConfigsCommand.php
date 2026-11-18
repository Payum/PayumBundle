<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Command;

use Exception;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'payum:security:reencrypt-gateway-configs',
    description: 'Encrypts every stored gateway config with the configured key, reading configs encrypted with the previous key or not encrypted at all.',
)]
final class ReencryptGatewayConfigsCommand extends Command
{
    /**
     * @param StorageInterface<object> $storage the gateway config storage, without the encryption decorator
     */
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly CypherInterface $cypher,
        private readonly ?CypherInterface $previousCypher = null,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 0;

        foreach ($this->storage->findBy([]) as $gatewayConfig) {
            if (! $gatewayConfig instanceof GatewayConfigInterface || ! $gatewayConfig instanceof CryptedInterface) {
                throw new LogicException(sprintf('The gateway config %s must implement %s and %s.', $gatewayConfig::class, GatewayConfigInterface::class, CryptedInterface::class));
            }

            $this->decrypt($gatewayConfig);

            // Without setConfig() a config stored as plain text is flagged as encrypted but keeps its plain values.
            $gatewayConfig->setConfig($gatewayConfig->getConfig());
            $gatewayConfig->encrypt($this->cypher);
            $this->storage->update($gatewayConfig);

            ++$count;
        }

        $output->writeln(sprintf('Re-encrypted %d gateway config(s).', $count));

        return Command::SUCCESS;
    }

    /**
     * Tries the current cypher first, so a run that stopped part-way can be started again.
     */
    private function decrypt(GatewayConfigInterface&CryptedInterface $gatewayConfig): void
    {
        try {
            $gatewayConfig->decrypt($this->cypher);

            return;
        } catch (Exception $e) {
            if (null === $this->previousCypher) {
                throw $this->cannotDecrypt($gatewayConfig, $e);
            }
        }

        try {
            $gatewayConfig->decrypt($this->previousCypher);
        } catch (Exception $e) {
            throw $this->cannotDecrypt($gatewayConfig, $e);
        }
    }

    private function cannotDecrypt(GatewayConfigInterface $gatewayConfig, Exception $previous): RuntimeException
    {
        return new RuntimeException(sprintf(
            'Cannot decrypt the config of gateway "%s" with the configured key%s.',
            $gatewayConfig->getGatewayName(),
            null === $this->previousCypher ? '. Set "payum.dynamic_gateways.encryption.previous" to the key it was encrypted with' : ' or the previous one'
        ), 0, $previous);
    }
}
