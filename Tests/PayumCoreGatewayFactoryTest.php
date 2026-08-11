<?php

declare(strict_types=1);

namespace Payum\Bundle\PayumBundle\Tests;

use DI\ContainerBuilder;
use Payum\Bundle\PayumBundle\Action\GetHttpRequestAction as SymfonyGetHttpRequestAction;
use Payum\Bundle\PayumBundle\Action\ObtainCreditCardAction;
use Payum\Bundle\PayumBundle\DI\SymfonyContainerAdapter;
use Payum\Bundle\PayumBundle\PayumCoreGatewayFactory;
use Payum\Bundle\PayumBundle\PayumVersion;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Extension\Context;
use Payum\Core\Gateway;
use Payum\Core\PayumBuilder;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Model\Token;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PayumCoreGatewayFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        if (! PayumVersion::supportsDependencyInjection()) {
            $this->markTestSkipped('PayumCoreGatewayFactory needs payum/core 2.0 or later.');
        }
    }

    public function testShouldImplementContainerConfiguration(): void
    {
        $rc = new ReflectionClass(PayumCoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(ContainerConfiguration::class));
    }

    public function testShouldUseTheHttpRequestActionOfTheBundle(): void
    {
        $symfonyAction = new SymfonyGetHttpRequestAction();

        $gateway = $this->createGateway([
            'payum.action.get_http_request' => $symfonyAction,
        ]);

        $this->assertContains($symfonyAction, $this->actionsOf($gateway));
        $this->assertNotContains(
            GetHttpRequestAction::class,
            array_map(get_class(...), $this->actionsOf($gateway))
        );
    }

    public function testShouldRegisterTheObtainCreditCardActionOfTheBundle(): void
    {
        $gateway = $this->createGateway();

        $this->assertContains(
            ObtainCreditCardAction::class,
            array_map(get_class(...), $this->actionsOf($gateway))
        );
    }

    public function testShouldAddTheActionsTaggedForAGateway(): void
    {
        $action = new TaggedAction();

        $gateway = $this->createGateway([
            PayumCoreGatewayFactory::ACTIONS => [
                [
                    'service' => $action,
                    'prepend' => false,
                ],
            ],
        ]);

        $this->assertContains($action, $this->actionsOf($gateway));
    }

    public function testShouldAddTheActionsSharedByEveryGateway(): void
    {
        $action = new TaggedAction();

        $gateway = $this->createGateway([
            PayumCoreGatewayFactory::SHARED_ACTIONS => [
                [
                    'service' => $action,
                    'prepend' => false,
                ],
            ],
        ]);

        $this->assertContains($action, $this->actionsOf($gateway));
    }

    public function testShouldPrependAnActionWhichAsksForIt(): void
    {
        $action = new TaggedAction();

        $gateway = $this->createGateway([
            PayumCoreGatewayFactory::ACTIONS => [
                [
                    'service' => $action,
                    'prepend' => true,
                ],
            ],
        ]);

        $this->assertSame($action, $this->actionsOf($gateway)[0]);
    }

    public function testShouldAddTheExtensionsTaggedForAGateway(): void
    {
        $extension = new TaggedExtension();

        $gateway = $this->createGateway([
            PayumCoreGatewayFactory::EXTENSIONS => [
                [
                    'service' => $extension,
                    'prepend' => false,
                ],
            ],
        ]);

        $this->assertContains($extension, $this->extensionsOf($gateway));
    }

    public function testShouldAddTheApisTaggedForAGateway(): void
    {
        $api = new \stdClass();

        $gateway = $this->createGateway([
            PayumCoreGatewayFactory::APIS => [
                [
                    'service' => $api,
                    'prepend' => false,
                ],
            ],
        ]);

        $reflected = (new ReflectionObject($gateway))->getProperty('apis');
        $reflected->setAccessible(true);

        $this->assertContains($api, $reflected->getValue($gateway));
    }

    public function testShouldIgnoreAnEntryWithoutAService(): void
    {
        $gateway = $this->createGateway([
            PayumCoreGatewayFactory::ACTIONS => [
                ['prepend' => false],
                [
                    'service' => 'not an object',
                ],
            ],
        ]);

        $this->assertInstanceOf(Gateway::class, $gateway);
    }

    /**
     * The reason the bundle hands Payum a global container: a service of the application can be
     * injected into the constructor of an action of a gateway.
     */
    public function testShouldInjectAServiceOfTheApplicationIntoAnAction(): void
    {
        $exchangeRates = new ExchangeRates();
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->setTokenStorage(new FilesystemStorage(sys_get_temp_dir(), Token::class, 'hash'))
            ->setTokenFactory($this->createMock(TokenFactoryInterface::class))
            ->setGlobalContainer(new SymfonyContainerAdapter(new ServiceLocator([
                ExchangeRates::class => static fn (): ExchangeRates => $exchangeRates,
            ])))
            ->setCoreGatewayFactory(static fn (array $config): PayumCoreGatewayFactory => new AutowiringGatewayFactory(
                array_replace($config, [
                    FormFactoryInterface::class => $formFactory,
                    RequestStack::class => new RequestStack(),
                ])
            ))
            ->addGateway('aGateway', [
                'factory' => 'core',
            ])
            ->getPayum()
        ;

        $autowired = null;
        foreach ($this->actionsOf($payum->getGateway('aGateway')) as $action) {
            if ($action instanceof AutowiredAction) {
                $autowired = $action;
            }
        }

        $this->assertInstanceOf(AutowiredAction::class, $autowired);
        $this->assertSame($exchangeRates, $autowired->exchangeRates);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createGateway(array $config = []): Gateway
    {
        $factory = new PayumCoreGatewayFactory(array_replace([
            FormFactoryInterface::class => $this->createMock(FormFactoryInterface::class),
            RequestStack::class => new RequestStack(),
        ], $config));

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($factory->configureContainer());

        return $factory->createGateway($containerBuilder->build());
    }

    /**
     * @return list<ActionInterface>
     */
    private function actionsOf(Gateway $gateway): array
    {
        $reflected = (new ReflectionObject($gateway))->getProperty('actions');
        $reflected->setAccessible(true);

        return array_values($reflected->getValue($gateway));
    }

    /**
     * @return list<ExtensionInterface>
     */
    private function extensionsOf(Gateway $gateway): array
    {
        $reflected = (new ReflectionObject($gateway))->getProperty('extensions');
        $reflected->setAccessible(true);

        $collection = $reflected->getValue($gateway);

        $reflected = (new ReflectionObject($collection))->getProperty('extensions');
        $reflected->setAccessible(true);

        return array_values($reflected->getValue($collection));
    }
}

class TaggedAction implements ActionInterface
{
    public function execute($request): void
    {
    }

    public function supports($request): bool
    {
        return false;
    }
}

class TaggedExtension implements ExtensionInterface
{
    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
    }
}

class ExchangeRates
{
}

class AutowiredAction implements ActionInterface
{
    public function __construct(
        public ExchangeRates $exchangeRates
    ) {
    }

    public function execute($request): void
    {
    }

    public function supports($request): bool
    {
        return false;
    }
}

class AutowiringGatewayFactory extends PayumCoreGatewayFactory
{
    public function getActions(): array
    {
        return array_merge(parent::getActions(), [
            AutowiredAction::class,
        ]);
    }
}
