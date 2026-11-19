<?php
namespace Payum\Bundle\PayumBundle\Profiler;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Event\CommandDispatched;
use Payum\Core\Event\CommandFailed;
use Payum\Core\Event\Event;
use Payum\Core\Event\FailureRaised;
use Payum\Core\Event\HandlerResolved;
use Payum\Core\Event\IdempotentResultReplayed;
use Payum\Core\Event\ResultReturned;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Event\WebhookReceived;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Gateway;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Shows in the profiler what Payum did during a request.
 *
 * With payum/core 1.x that is the tree of actions an extension sees. From 2.0 on it is also every
 * command, as the events Payum dispatches report it: the gateway, the handler, what came back, the
 * status changes and webhooks along the way, and the middleware each gateway ran it through. Every
 * command and action is timed on the Performance panel too, in the "payum" category.
 *
 * Next to that, what the application configured: the gateways, where they come from, storages,
 * locking and idempotency.
 */
class PayumCollector extends DataCollector implements ExtensionInterface
{
    /**
     * @var Context[]
     */
    private array $contexts = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $commands = [];

    /**
     * The index in $commands of each command being dispatched, by its handler context.
     */
    private \WeakMap $commandIndexes;

    /**
     * @param array<string, mixed> $configuration a summary of the bundle's configuration, without secrets
     * @param \Closure|null $registry returns the Payum registry; a closure so building the collector builds no gateway
     * @param \Closure|null $dynamicConfigStorage returns the storage of the dynamic gateway configs, if there is one
     */
    public function __construct(
        private readonly array $configuration = [],
        private readonly ?\Closure $registry = null,
        private readonly ?\Closure $dynamicConfigStorage = null,
        private readonly ?Stopwatch $stopwatch = null,
    ) {
        $this->reset();
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $actions = [];
        foreach ($this->contexts as $context) {
            $actions[] = $this->collectAction($context);
        }

        $dynamicGateways = $this->dynamicGateways();

        $gatewaysUsed = array_values(array_unique(array_filter(array_column($this->commands, 'gateway'))));

        $pipelines = [];
        foreach ($gatewaysUsed as $gatewayName) {
            $pipelines[$gatewayName] = $this->pipelineOf($gatewayName);
        }

        $commands = $this->commands;
        foreach ($commands as &$command) {
            $command['source'] = isset($dynamicGateways[$command['gateway']]) ? 'dynamic' : 'static';
        }
        unset($command);

        $this->data = [
            'actions' => $actions,
            'commands' => $commands,
            'pipelines' => $pipelines,
            'gateways' => $this->configuration['gateways'] ?? [],
            'dynamic_gateways' => $dynamicGateways,
            'configuration' => $this->configuration,
            'dynamic_gateways_error' => $this->data['dynamic_gateways_error'] ?? null,
        ];
    }

    public function getName(): string
    {
        return 'payum';
    }

    public function reset(): void
    {
        $this->contexts = [];
        $this->commands = [];
        $this->commandIndexes = new \WeakMap();
        $this->data = [
            'actions' => [],
            'commands' => [],
            'pipelines' => [],
            'gateways' => [],
            'dynamic_gateways' => [],
            'configuration' => [],
        ];
    }

    // 1.x extension

    public function onPreExecute(Context $context): void
    {
        // a 2.0 command passing through LegacyExtensionMiddleware, which the events already report
        if ($context->getRequest() instanceof CommandInterface) {
            return;
        }

        $this->contexts[] = $context;

        $this->stopwatch?->start($this->actionEventName($context), 'payum');
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
        $name = $this->actionEventName($context);

        if ($this->stopwatch?->isStarted($name)) {
            $this->stopwatch->stop($name);
        }
    }

    // 2.0 events, dispatched through the application's event dispatcher

    public function onCommandDispatched(CommandDispatched $event): void
    {
        $context = $event->context;
        $command = $event->command;
        $token = $command->token();
        $subject = $command->subject();

        $this->commandIndexes[$context] = count($this->commands);

        $this->commands[] = [
            'depth' => count($context->previous()),
            'gateway' => $context->gatewayName(),
            'command_class' => $command::class,
            'command_short_class' => $this->getShortClass($command),
            'command' => $this->cloneVar($command),
            'idempotency_key' => $context->idempotencyKey(),
            'token' => null === $token ? null : [
                'hash' => $token->getHash(),
                'target_url' => $token->getTargetUrl(),
                'after_url' => $token->getAfterUrl(),
            ],
            // read off the command, not the context, which would load the payment before the lock is taken
            'subject_class' => null === $subject ? null : $subject::class,
            'handler_class' => null,
            'replayed' => false,
            'webhook' => null,
            'status_changes' => [],
            'result' => null,
            'failure' => null,
            'exception' => null,
            'started_at' => microtime(true),
            'duration' => null,
        ];

        $this->stopwatch?->start($this->commandEventName($event), 'payum');
    }

    public function onHandlerResolved(HandlerResolved $event): void
    {
        $this->update($event, ['handler_class' => $event->handler::class]);
    }

    public function onIdempotentResultReplayed(IdempotentResultReplayed $event): void
    {
        $this->update($event, ['replayed' => true]);
    }

    public function onWebhookReceived(WebhookReceived $event): void
    {
        $this->update($event, ['webhook' => $this->cloneVar($event->webhook)]);
    }

    public function onStatusChanged(StatusChanged $event): void
    {
        if (null === $index = $this->indexOf($event)) {
            return;
        }

        $this->commands[$index]['status_changes'][] = [
            'from' => $event->from?->value,
            'to' => $event->to->value,
            'subject_class' => $event->subject::class,
        ];
    }

    public function onResultReturned(ResultReturned $event): void
    {
        $result = $event->result;

        $this->finish($event, [
            'result' => [
                'class' => $result::class,
                'short_class' => $this->getShortClass($result),
                'status' => $result->status?->value,
                'next' => null === $result->next ? null : $this->getShortClass($result->next),
                'transaction_id' => $result->transactionId,
                'successful' => $result->isSuccessful(),
                'failed' => $result->isFailed(),
                'requires_interaction' => $result->requiresInteraction(),
                'dump' => $this->cloneVar($result),
            ],
        ]);
    }

    public function onFailureRaised(FailureRaised $event): void
    {
        $this->update($event, ['failure' => $this->cloneVar($event->failure)]);
    }

    public function onCommandFailed(CommandFailed $event): void
    {
        $this->finish($event, [
            'exception' => [
                'class' => $event->exception::class,
                'short_class' => $this->getShortClass($event->exception),
                'message' => $event->exception->getMessage(),
                'dump' => $this->cloneVar($event->exception),
            ],
        ]);
    }

    // read by the template

    public function getActions(): array
    {
        return $this->data['actions'];
    }

    public function getCommands(): array
    {
        return $this->data['commands'];
    }

    /**
     * @return array<string, list<class-string>|null> by gateway name, null when unknown
     */
    public function getPipelines(): array
    {
        return $this->data['pipelines'];
    }

    public function getGateways(): array
    {
        return $this->data['gateways'];
    }

    public function getDynamicGateways(): array
    {
        return $this->data['dynamic_gateways'];
    }

    public function getDynamicGatewaysError(): ?string
    {
        return $this->data['dynamic_gateways_error'] ?? null;
    }

    public function getConfiguration(): array
    {
        return $this->data['configuration'];
    }

    public function getFailureCount(): int
    {
        return count(array_filter(
            $this->data['commands'],
            static fn (array $command): bool => null !== $command['exception'] || ($command['result']['failed'] ?? false),
        ));
    }

    public function getDuration(): float
    {
        // the outermost commands only, so a nested one is not counted twice
        return array_sum(array_column(array_filter(
            $this->data['commands'],
            static fn (array $command): bool => 0 === $command['depth'],
        ), 'duration'));
    }

    public function dump(): string
    {
        $str = '';

        foreach ($this->data['actions'] as $index => $contextData) {
            if ($contextData['deep'] === 0) {
                $str .= $this->formatRequest($contextData).PHP_EOL;

                continue;
            }

            if ($contextData['action_class']) {
                $str .= $this->formatAction($contextData).PHP_EOL;
            }

            if (false === array_key_exists($index + 1, $this->data['actions']) && $contextData['reply_class']) {
                $str .= $this->formatReply($contextData).PHP_EOL;
            }

            if (false === array_key_exists($index + 1, $this->data['actions']) && $contextData['exception_class']) {
                $str .= $this->formatException($contextData).PHP_EOL;
            }
        }

        return $str;
    }

    protected function formatAction(array $contextData): string
    {
        return sprintf(
            '%s└ %s::execute(%s)',
            str_repeat(' ', $contextData['deep'] - 1),
            $contextData['action_short_class'],
            $this->formatRequest($contextData)
        );
    }

    protected function formatReply(array $contextData): string
    {
        return sprintf(
            '%s reply %s',
            sprintf('%s ⬅', str_repeat(' ', $contextData['deep'])),
            $contextData['reply_short_class']
        );
    }

    protected function formatException(array $contextData): string
    {
        return sprintf(
            '%s exception %s',
            sprintf('%s ⬅', str_repeat(' ', $contextData['deep'])),
            $contextData['exception_short_class']
        );
    }

    protected function formatRequest(array $contextData): string
    {
        return sprintf(
            '%s[%s]',
            $contextData['request_short_class'],
            $contextData['model_short_class']
        );
    }

    private function collectAction(Context $context): array
    {
        $request = $context->getRequest();

        $contextData = [
            'deep' => count($context->getPrevious()),
            'request_class' => get_class($request),
            'request_short_class' => $this->getShortClass($request),
            'model_class' => null,
            'model_short_class' => null,
            'action_class' => null,
            'action_short_class' => null,
            'exception_class' => null,
            'exception_short_class' => null,
            'reply_class' => null,
            'reply_short_class' => null,
        ];

        if ($request instanceof Generic) {
            $contextData['model_class'] = get_debug_type($request->getModel());
            $contextData['model_short_class'] = is_object($request->getModel()) ?
                $this->getShortClass($request->getModel()) :
                gettype($request->getModel())
            ;
        }

        if ($context->getAction()) {
            $contextData['action_class'] = get_class($context->getAction());
            $contextData['action_short_class'] = $this->getShortClass($context->getAction());
        }

        if ($context->getException()) {
            $contextData['exception_class'] = get_class($context->getException());
            $contextData['exception_short_class'] = $this->getShortClass($context->getException());
        }

        if ($context->getReply()) {
            $contextData['reply_class'] = get_class($context->getReply());
            $contextData['reply_short_class'] = $this->getShortClass($context->getReply());
        }

        return $contextData;
    }

    private function indexOf(Event $event): ?int
    {
        return $this->commandIndexes[$event->context] ?? null;
    }

    private function update(Event $event, array $data): void
    {
        if (null !== $index = $this->indexOf($event)) {
            $this->commands[$index] = array_replace($this->commands[$index], $data);
        }
    }

    private function finish(Event $event, array $data): void
    {
        if (null === $index = $this->indexOf($event)) {
            return;
        }

        $data['duration'] = (microtime(true) - $this->commands[$index]['started_at']) * 1000;
        $this->update($event, $data);

        $name = $this->commandEventName($event);
        if ($this->stopwatch?->isStarted($name)) {
            $this->stopwatch->stop($name);
        }
    }

    /**
     * The middleware a gateway runs every command through, outermost first.
     *
     * @return list<class-string>|null
     */
    private function pipelineOf(string $gatewayName): ?array
    {
        try {
            $gateway = ($this->registry)?->__invoke()?->getGateway($gatewayName);
        } catch (\Throwable) {
            return null;
        }

        if (! $gateway instanceof Gateway) {
            return null;
        }

        return array_map(static fn (object $one): string => $one::class, $gateway->middleware());
    }

    /**
     * @return array<string, string> factory name by gateway name, for the gateways stored as dynamic configs
     */
    private function dynamicGateways(): array
    {
        try {
            $storage = ($this->dynamicConfigStorage)?->__invoke();
            if (! $storage instanceof StorageInterface) {
                return [];
            }

            $gateways = [];
            foreach ($storage->findBy([]) as $config) {
                if ($config instanceof GatewayConfigInterface) {
                    $gateways[$config->getGatewayName()] = $config->getFactoryName();
                }
            }

            return $gateways;
        } catch (\Throwable $e) {
            $this->data['dynamic_gateways_error'] = $e->getMessage();

            return [];
        }
    }

    private function commandEventName(Event $event): string
    {
        return sprintf('payum: %s (%s)', $this->getShortClass($event->command), $event->context->gatewayName() ?? 'unnamed');
    }

    private function actionEventName(Context $context): string
    {
        return sprintf('payum: %s', $this->getShortClass($context->getRequest()));
    }

    private function getShortClass(object $object): string
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}
