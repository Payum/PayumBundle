<?php


namespace Functional\Twig;


use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Payum;
use Twig\Environment;

class PathRegistrarTest extends WebTestCase
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var Payum
     */
    private $payum;

    protected function setUp()
    {
        parent::setUp();

        $this->twig = static::$container->get('twig');
        $this->payum = static::$container->get('payum');
    }


    public function testPathsAreConfigured()
    {
        $this->payum->getGateway('barGateway');

        $templateContent = $this->twig->render('@PayumPaypalExpressCheckout/confirmOrder.html.twig');

        $this->assertContains(
            '<input type="submit" name="confirm" value="I confirm order purchase">',
            $templateContent
        );
    }

}
