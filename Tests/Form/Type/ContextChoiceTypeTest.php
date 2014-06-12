<?php
namespace Payum\Bundle\PayumBundle\Tests\Form\Type;

use Payum\Bundle\PayumBundle\Form\Type\ContextChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContextChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractType()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Form\Type\ContextChoiceType');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithContextNamesAsFirstArgument()
    {
        new ContextChoiceType(array('foo', 'bar'));
    }

    /**
     * @test
     */
    public function shouldExtendChoiceType()
    {
        $type = new ContextChoiceType(array('foo', 'bar'));

        $this->assertEquals('choice', $type->getParent());
    }

    /**
     * @test
     */
    public function shouldReturnExpectedName()
    {
        $type = new ContextChoiceType(array('foo', 'bar'));

        $this->assertEquals('payum_context_choice', $type->getName());
    }

    /**
     * @test
     */
    public function shouldAllowResolveOptions()
    {
        $type = new ContextChoiceType(array('foo', 'bar'));

        $resolver = new OptionsResolver;

        $type->setDefaultOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('choices', $options);
        $this->assertEquals(array('foo' => 'context.foo', 'bar' => 'context.bar'), $options['choices']);

        $this->assertArrayHasKey('translation_domain', $options);
        $this->assertEquals('PayumBundle', $options['translation_domain']);
    }

}