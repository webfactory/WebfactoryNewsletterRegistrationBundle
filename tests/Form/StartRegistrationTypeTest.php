<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Form;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Form\HoneypotType;
use Webfactory\NewsletterRegistrationBundle\Form\StartRegistrationType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;

final class StartRegistrationTypeTest extends TypeTestCase
{
    /** @var NewsletterRepositoryInterface|MockObject */
    private $newsletterRepository;

    /** @var PendingOptInRepositoryInterface|MockObject */
    private $pendingOptInRepository;

    /** @var RecipientRepositoryInterface|MockObject */
    private $recipientRepository;

    /** @var Newsletter|null */
    private $newsletter1;

    /** @var Newsletter|null */
    private $newsletter2;

    public function setUp(): void
    {
        $this->newsletterRepository = $this->createMock(NewsletterRepositoryInterface::class);
        $this->pendingOptInRepository = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->recipientRepository = $this->createMock(RecipientRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * @test
     */
    public function view_has_no_newsletter_choices_element_if_there_are_no_choices(): void
    {
        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $this->assertArrayNotHasKey('newsletters', $formView->vars['form']->children);
    }

    /**
     * @test
     */
    public function view_has_no_newsletter_choice_element_if_there_is_exactly_one_choice(): void
    {
        $this->setUpOneNewsletter();

        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $this->assertArrayNotHasKey('newsletters', $formView->vars['form']->children);
    }

    /**
     * @test
     */
    public function view_contains_newsletter_choice_element_if_there_is_more_than_one_choice(): void
    {
        $this->setUpTwoNewsletters();

        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $newslettersVars = $formView->vars['form']->children['newsletters']->vars;
        $this->assertArrayHasKey('choices', $newslettersVars);

        $this->assertCount(2, $newslettersVars['choices']);
        $this->assertEquals($this->newsletter1->getId(), $newslettersVars['choices'][0]->value);
        $this->assertEquals($this->newsletter1->getName(), $newslettersVars['choices'][0]->label);
        $this->assertEquals($this->newsletter2->getId(), $newslettersVars['choices'][1]->value);
        $this->assertEquals($this->newsletter2->getName(), $newslettersVars['choices'][1]->label);
    }

    /**
     * @test
     */
    public function does_not_validate_without_honeypot()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => 'webfactory@example.com',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors());
        $this->assertEquals(
            HoneypotType::ERROR_MESSAGE_HONEYPOT_NOT_SUBMITTED,
            $form->getErrors()->current()->getMessage()
        );
    }

    /**
     * @test
     */
    public function does_not_validate_with_filled_honeypot()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => 'webfactory@example.com',
            'url' => 'http://spam.com',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors());
        $this->assertEquals(HoneypotType::ERROR_MESSAGE_HONEYPOT_FILLED, $form->getErrors()->current()->getMessage());
    }

    /**
     * @test
     */
    public function does_not_validate_without_email_address()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => '',
            'url' => '',
        ]);
        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals((new NotBlank())->message, $form->getErrors(true, true)->current()->getMessage());
    }

    /**
     * @test
     */
    public function does_not_validate_with_invalid_email_address()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => 'this is no valid email address',
            'url' => '',
        ]);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals((new Email())->message, $form->getErrors(true, true)->current()->getMessage());
    }

    /**
     * @test
     */
    public function does_not_validate_with_already_registering_email_address()
    {
        $this->pendingOptInRepository
            ->method('isEmailAddressHashAlreadyRegistered')
            ->with($this->callback(
                function (?string $emailAddressHash) {
                    return !empty($emailAddressHash);
                }
            ))
            ->willReturn(true);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => 'webfactory@example.com',
            'url' => '',
        ]);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals(
            StartRegistrationType::ERROR_EMAIL_ALREADY_REGISTERING,
            $form->getErrors(true, true)->current()->getMessage()
        );
    }

    /**
     * @test
     */
    public function does_not_validate_with_already_registered_email_address()
    {
        $this->recipientRepository
            ->method('isEmailAddressAlreadyRegistered')
            ->with('webfactory@example.com')
            ->willReturn(true);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => 'webfactory@example.com',
            'url' => '',
        ]);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals(
            StartRegistrationType::ERROR_EMAIL_ALREADY_REGISTERED,
            $form->getErrors(true, true)->current()->getMessage()
        );
    }

    /**
     * @test
     */
    public function does_not_validate_if_newsletter_choices_exist_but_none_was_selected()
    {
        $this->setUpTwoNewsletters();

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => 'webfactory@example.com',
            'newsletters' => [],
            'url' => '',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals(
            'You must select at least 1 choice.',
            $form->getErrors(true, true)->current()->getMessage()
        );
    }

    /**
     * @test
     */
    public function provides_data_if_submitted_with_valid_data_without_newsletter_choices()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => 'webfactory@example.com',
            'url' => '',
        ]);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $data = $form->getData();
        $this->assertEquals('webfactory@example.com', $data['emailAddress']);
    }

    /**
     * @test
     */
    public function provides_data_if_submitted_with_valid_data_and_newsletter_choices()
    {
        $this->setUpTwoNewsletters();

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            'emailAddress' => 'webfactory@example.com',
            'newsletters' => [$this->newsletter1->getId(), $this->newsletter2->getId()],
            'url' => '',
        ]);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $data = $form->getData();
        $this->assertEquals('webfactory@example.com', $data['emailAddress']);
        $this->assertEquals([$this->newsletter1, $this->newsletter2], $data['newsletters']);
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    new StartRegistrationType(
                        $this->newsletterRepository,
                        $this->pendingOptInRepository,
                        $this->recipientRepository,
                        'secret'
                    ),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    private function setUpOneNewsletter(): void
    {
        $this->newsletter1 = new Newsletter(1, 'Newsletter 1');
        $this->newsletterRepository->method('findVisible')->willReturn([$this->newsletter1]);
    }

    private function setUpTwoNewsletters(): void
    {
        $this->newsletter1 = new Newsletter(1, 'Newsletter 1');
        $this->newsletter2 = new Newsletter(2, 'Newsletter 2');
        $this->newsletterRepository->method('findVisible')->willReturn([$this->newsletter1, $this->newsletter2]);
    }
}
