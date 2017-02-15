<?php
namespace Payum\Bundle\PayumBundle\Profiler;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Generic;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PayumCollector extends DataCollector implements ExtensionInterface
{
    /**
     * @var Context[]
     */
    private $contexts = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
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
                $contextData['model_class'] = is_object($request->getModel()) ?
                    get_class($request->getModel()) :
                    gettype($request->getModel())
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
    public function getName()
    {
        return 'payum';
    }

    /**
     * {@inheritdoc}
     */
    public function onPreExecute(Context $context)
    {
        $this->contexts[] = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function onExecute(Context $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onPostExecute(Context $context)
    {
    }

    /**
     * @param object $object
     *
     * @return string
     */
    private function getShortClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    public function dump()
    {
        $str = '';
        $previousContext = null;

        foreach ($this->data as $index => $contextData) {
            if ($contextData['deep'] == 0) {
                $str .= $this->formatRequest($contextData).PHP_EOL;

                continue;
            }

            if ($contextData['action_class']) {
                $str .= $this->formatAction($contextData).PHP_EOL;
            }

            if (false == array_key_exists($index + 1, $this->data) && $contextData['reply_class']) {
                $str .= $this->formatReply($contextData).PHP_EOL;
            }

            if (false == array_key_exists($index + 1, $this->data) && $contextData['exception_class']) {
                $str .= $this->formatException($contextData).PHP_EOL;
            }
        }

        return $str;
    }

    protected function formatAction(array $contextData)
    {
        return sprintf(
            '%s└ %s::execute(%s)',
            str_repeat(' ', $contextData['deep'] - 1),
            $contextData['action_short_class'],
            $this->formatRequest($contextData)
        );
    }

    protected function formatReply(array $contextData)
    {
        $str = sprintf(
            '%s reply %s',
            sprintf('%s ⬅', str_repeat(' ', $contextData['deep'])),
            $contextData['reply_short_class']
        );

        return $str;
    }

    protected function formatException(array $contextData)
    {
        $str = sprintf(
            '%s exception %s',
            sprintf('%s ⬅', str_repeat(' ', $contextData['deep'])),
            $contextData['exception_short_class']
        );

        return $str;
    }

    protected function formatRequest(array $contextData)
    {
        return sprintf(
            '%s[%s]',
            $contextData['request_short_class'],
            $contextData['model_short_class']
        );
    }
}
