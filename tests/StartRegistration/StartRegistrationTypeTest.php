<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\StartRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactory;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\EmailAddressType;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\HoneypotType;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

final class StartRegistrationTypeTest extends TypeTestCase
{
    protected const MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS = 1;

    /** @var NewsletterRepositoryInterface|MockObject */
    private $newsletterRepository;

    /** @var PendingOptInFactoryInterface|MockObject */
    private $pendingOptInFactory;

    /** @var PendingOptInRepositoryInterface|MockObject */
    private $pendingOptInRepository;

    /** @var RecipientRepositoryInterface|MockObject */
    private $recipientRepository;

    /** @var EmailAddressFactoryInterface */
    private $emailAddressFactory;

    /** @var Newsletter|null */
    private $newsletter1;

    /** @var Newsletter|null */
    private $newsletter2;

    public function setUp(): void
    {
        $this->newsletterRepository = $this->createMock(NewsletterRepositoryInterface::class);
        $this->pendingOptInFactory = $this->createMock(PendingOptInFactoryInterface::class);
        $this->pendingOptInRepository = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->recipientRepository = $this->createMock(RecipientRepositoryInterface::class);
        $this->emailAddressFactory = new EmailAddressFactory('secret');
        parent::setUp();
    }

    /**
     * @test
     */
    public function view_has_no_newsletter_choices_element_if_there_are_no_choices(): void
    {
        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $this->assertArrayNotHasKey(startRegistrationType::ELEMENT_NEWSLETTERS, $formView->vars['form']->children);
    }

    /**
     * @test
     */
    public function view_has_no_newsletter_choice_element_if_there_is_exactly_one_choice(): void
    {
        $this->setUpOneNewsletter();

        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $this->assertArrayNotHasKey(startRegistrationType::ELEMENT_NEWSLETTERS, $formView->vars['form']->children);
    }

    /**
     * @test
     */
    public function view_contains_newsletter_choice_element_if_there_is_more_than_one_choice(): void
    {
        $this->setUpTwoNewsletters();

        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $newslettersVars = $formView->vars['form']->children[startRegistrationType::ELEMENT_NEWSLETTERS]->vars;
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
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
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
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            startRegistrationType::ELEMENT_HONEYPOT => 'http://spam.com',
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
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => '',
            startRegistrationType::ELEMENT_HONEYPOT => '',
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
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'this is no valid email address',
            startRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals((new Email())->message, $form->getErrors(true, true)->current()->getMessage());
    }

    /**
     * @test
     */
    public function does_not_validate_with_already_registering_email_address_if_not_enough_time_has_passed()
    {
        $veryRecentPendingOptIn = new PendingOptIn(null, new EmailAddress('webfactory@example.com', 'secret'));
        $this->pendingOptInRepository
            ->method('findByEmailAddress')
            ->willReturn($veryRecentPendingOptIn);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            startRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));

        // The error message is customized with a time variable, so we compare only the static text at the beginning
        $this->assertEquals(
            substr(EmailAddressType::ERROR_OPT_IN_EMAIL_LIMIT_REACHED, 0, 100),
            substr($form->getErrors(true, true)->current()->getMessage(), 0, 100)
        );
    }

    /**
     * @test
     */
    public function does_validate_with_already_registering_email_address_if_enough_time_has_passed()
    {
        $oldPendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new \DateTimeImmutable('-'.(self::MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS + 1).' hour')
        );
        $this->pendingOptInRepository
            ->method('findByEmailAddress')
            ->willReturn($oldPendingOptIn);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            startRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isValid());
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
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            startRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals(
            EmailAddressType::ERROR_EMAIL_ALREADY_REGISTERED,
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
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            startRegistrationType::ELEMENT_NEWSLETTERS => [],
            startRegistrationType::ELEMENT_HONEYPOT => '',
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
    public function provides_PendingOptIn_if_submitted_with_valid_data_without_newsletter_choices()
    {
        $pendingOptIn = new PendingOptIn(null, new EmailAddress('webfactory@example.com', 'secret'));
        $this->pendingOptInFactory
            ->method('fromRegistrationFormData')
            ->with(
                $this->callback(
                    function (array $formData) {
                        return \array_key_exists(StartRegistrationType::ELEMENT_EMAIL_ADDRESS, $formData)
                            && $formData[StartRegistrationType::ELEMENT_EMAIL_ADDRESS] instanceof EmailAddress
                            && 'webfactory@example.com' === (string) $formData[StartRegistrationType::ELEMENT_EMAIL_ADDRESS]->getEmailAddress();
                    }
                )
            )
            ->willReturn($pendingOptIn);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            startRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEquals($pendingOptIn, $form->getData());
    }

    /**
     * @test
     */
    public function provides_PendingOptIn_if_submitted_with_valid_data_and_newsletter_choices()
    {
        $this->setUpTwoNewsletters();

        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [$this->newsletter1, $this->newsletter2]
        );
        $this->pendingOptInFactory
            ->method('fromRegistrationFormData')
            ->with(
                $this->callback(
                    function (array $formData) {
                        return \array_key_exists(StartRegistrationType::ELEMENT_EMAIL_ADDRESS, $formData)
                            && $formData[StartRegistrationType::ELEMENT_EMAIL_ADDRESS] instanceof EmailAddress
                            && 'webfactory@example.com' === (string) $formData[StartRegistrationType::ELEMENT_EMAIL_ADDRESS]->getEmailAddress()
                            && \array_key_exists(StartRegistrationType::ELEMENT_NEWSLETTERS, $formData)
                            && $formData[StartRegistrationType::ELEMENT_NEWSLETTERS] === [$this->newsletter1, $this->newsletter2];
                    }
                )
            )
            ->willReturn($pendingOptIn);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            startRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            startRegistrationType::ELEMENT_NEWSLETTERS => [$this->newsletter1->getId(), $this->newsletter2->getId()],
            startRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($pendingOptIn, $form->getData());
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    new StartRegistrationType($this->newsletterRepository, $this->pendingOptInFactory),
                    new EmailAddressType(
                        $this->pendingOptInRepository,
                        $this->recipientRepository,
                        $this->emailAddressFactory,
                        self::MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS
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
