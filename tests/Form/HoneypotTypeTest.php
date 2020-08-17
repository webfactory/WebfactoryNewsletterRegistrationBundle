<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Webfactory\NewsletterRegistrationBundle\Form\HoneypotType;

final class HoneypotTypeTest extends TypeTestCase
{
    /** @var FormInterface */
    private $form;

    protected function setUp(): void
    {
        parent::setUp();
        $this->form = $this->factory->createBuilder()->add('url', HoneypotType::class)->getForm();
    }

    /**
     * @test
     */
    public function is_valid_if_empty_honeypot_is_submitted(): void
    {
        $this->form->submit(['url' => '']);

        $this->assertTrue($this->form->isValid());
    }

    /**
     * @test
     */
    public function is_not_valid_if_honeypot_is_not_submitted_at_all(): void
    {
        $this->form->submit([]);

        $this->assertFalse($this->form->isValid());

        $errors = $this->form->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals(HoneypotType::ERROR_MESSAGE_HONEYPOT_NOT_SUBMITTED, $errors->current()->getMessage());
    }

    /**
     * @test
     */
    public function is_not_valid_if_honeypot_was_filled_in(): void
    {
        $this->form->submit(['url' => 'spam-url']);

        $this->assertFalse($this->form->isValid());

        $errors = $this->form->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals(HoneypotType::ERROR_MESSAGE_HONEYPOT_FILLED, $errors->current()->getMessage());
    }
}
