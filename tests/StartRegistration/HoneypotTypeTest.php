<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\StartRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\HoneypotType;

final class HoneypotTypeTest extends TypeTestCase
{
    /** @var FormInterface */
    private $form;

    /** @var TranslatorInterface|MockObject */
    private $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnArgument(0);
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

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    new HoneypotType($this->translator),
                ],
                []
            ),
        ];
    }
}
