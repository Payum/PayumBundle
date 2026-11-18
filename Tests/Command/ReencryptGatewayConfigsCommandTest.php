<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests\Command;

use Defuse\Crypto\Key;
use ParagonIE\Halite\KeyFactory;
use Payum\Bundle\PayumBundle\Command\ReencryptGatewayConfigsCommand;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Bridge\Halite\Security\HaliteCypher;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Security\CypherInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ReencryptGatewayConfigsCommandTest extends TestCase
{
    private const CONFIG = ['apiKey' => 'sk_test_123', 'sandbox' => true];

    /**
     * @test
     */
    public function shouldEncryptConfigsStoredAsPlainText(): void
    {
        $cypher = $this->defuse();
        $gatewayConfig = $this->gatewayConfig();

        $output = $this->execute([$gatewayConfig], $cypher);

        $this->assertStringContainsString('Re-encrypted 1 gateway config(s).', $output);
        $this->assertDecryptsWith($cypher, $gatewayConfig);
    }

    /**
     * @test
     */
    public function shouldReEncryptConfigsWithANewKey(): void
    {
        $old = $this->defuse();
        $new = $this->defuse();
        $gatewayConfig = $this->gatewayConfig($old);

        $this->execute([$gatewayConfig], $new, $old);

        $this->assertDecryptsWith($new, $gatewayConfig);
        $this->assertDoesNotDecryptWith($old, $gatewayConfig);
    }

    /**
     * @test
     */
    public function shouldReEncryptConfigsFromDefuseToHalite(): void
    {
        if (! class_exists(HaliteCypher::class) || ! class_exists(KeyFactory::class)) {
            $this->markTestSkipped('Needs payum/core 2.0 and paragonie/halite.');
        }

        $defuse = $this->defuse();
        $halite = new HaliteCypher(KeyFactory::generateEncryptionKey());
        $gatewayConfig = $this->gatewayConfig($defuse);

        $this->execute([$gatewayConfig], $halite, $defuse);

        $this->assertDecryptsWith($halite, $gatewayConfig);
        $this->assertDoesNotDecryptWith($defuse, $gatewayConfig);
    }

    /**
     * @test
     */
    public function shouldAcceptConfigsAlreadyEncryptedWithTheCurrentKeySoAStoppedRunCanBeRepeated(): void
    {
        $old = $this->defuse();
        $new = $this->defuse();
        $done = $this->gatewayConfig($new);
        $pending = $this->gatewayConfig($old);

        $this->execute([$done, $pending], $new, $old);

        $this->assertDecryptsWith($new, $done);
        $this->assertDecryptsWith($new, $pending);
    }

    /**
     * @test
     */
    public function shouldFailOnAConfigNeitherKeyDecrypts(): void
    {
        $gatewayConfig = $this->gatewayConfig($this->defuse());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot decrypt the config of gateway "acme"');

        $this->execute([$gatewayConfig], $this->defuse(), $this->defuse(), 0);
    }

    /**
     * @param GatewayConfig[] $gatewayConfigs
     */
    private function execute(array $gatewayConfigs, CypherInterface $cypher, ?CypherInterface $previousCypher = null, ?int $updates = null): string
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('findBy')->willReturn($gatewayConfigs);
        $storage->expects($this->exactly($updates ?? count($gatewayConfigs)))->method('update');

        $tester = new CommandTester(new ReencryptGatewayConfigsCommand($storage, $cypher, $previousCypher));
        $tester->execute([]);

        return $tester->getDisplay();
    }

    private function gatewayConfig(?CypherInterface $cypher = null): GatewayConfig
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('acme');
        $gatewayConfig->setConfig(self::CONFIG);

        if ($cypher) {
            $gatewayConfig->encrypt($cypher);
        }

        return $gatewayConfig;
    }

    private function defuse(): DefuseCypher
    {
        return new DefuseCypher(Key::createNewRandomKey()->saveToAsciiSafeString());
    }

    private function assertDecryptsWith(CypherInterface $cypher, GatewayConfig $gatewayConfig): void
    {
        $gatewayConfig->decrypt($cypher);

        $this->assertSame(self::CONFIG + ['encrypted' => true], $gatewayConfig->getConfig());
    }

    private function assertDoesNotDecryptWith(CypherInterface $cypher, GatewayConfig $gatewayConfig): void
    {
        try {
            $gatewayConfig->decrypt($cypher);
        } catch (\Exception) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->fail('The config still decrypts with the old key.');
    }
}
