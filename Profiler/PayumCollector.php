<?php
namespace Payum\Bundle\PayumBundle\Profiler;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Generic;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\Data;

class PayumCollector extends DataCollector implements ExtensionInterface
{
    /**
     * @var Context[]
     */
    private array $contexts = [];

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        foreach ($this->contexts as $context) {
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
                $contextData['model_class'] = get_debug_type($request->getModel())
                ;

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

            $this->data[] = $contextData;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'payum';
    }

    /**
     * {@inheritdoc}
     */
    public function onPreExecute(Context $context): void
    {
        $this->contexts[] = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function onExecute(Context $context): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onPostExecute(Context $context): void
    {
    }

    private function getShortClass(object $object): string
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    public function dump(): string
    {
        $str = '';
        $previousContext = null;

        foreach ($this->data as $index => $contextData) {
            if ($contextData['deep'] === 0) {
                $str .= $this->formatRequest($contextData).PHP_EOL;

                continue;
            }

            if ($contextData['action_class']) {
                $str .= $this->formatAction($contextData).PHP_EOL;
            }

            if (false === array_key_exists($index + 1, $this->data) && $contextData['reply_class']) {
                $str .= $this->formatReply($contextData).PHP_EOL;
            }

            if (false === array_key_exists($index + 1, $this->data) && $contextData['exception_class']) {
                $str .= $this->formatException($contextData).PHP_EOL;
            }
        }

        return $str;
    }

    public function reset(): void
    {
        $this->contexts = [];
        $this->data = [];
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
}
