<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\StartRegistration;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\CategoryRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactory;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\EmailAddressType;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\HoneypotType;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class TypeTest extends TypeTestCase
{
    protected const MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS = 1;

    protected CategoryRepositoryInterface&MockObject $categoryRepository;
    protected PendingOptInFactoryInterface&MockObject $pendingOptInFactory;
    protected BlockedEmailAddressHashRepositoryInterface&MockObject $blockedEmailAddressHashRepository;
    protected PendingOptInRepositoryInterface&MockObject $pendingOptInRepository;
    protected RecipientRepositoryInterface&MockObject $recipientRepository;
    protected EmailAddressFactoryInterface $emailAddressFactory;
    protected TranslatorInterface&MockObject $translator;
    protected ?Category $category1;
    protected ?Category $category2;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->pendingOptInFactory = $this->createMock(PendingOptInFactoryInterface::class);
        $this->blockedEmailAddressHashRepository = $this->createMock(BlockedEmailAddressHashRepositoryInterface::class);
        $this->pendingOptInRepository = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->recipientRepository = $this->createMock(RecipientRepositoryInterface::class);
        $this->emailAddressFactory = new EmailAddressFactory('secret');
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnArgument(0);

        parent::setUp();
    }

    #[Test]
    public function view_has_no_category_choices_element_if_there_are_no_choices(): void
    {
        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $this->assertArrayNotHasKey(StartRegistrationType::ELEMENT_CATEGORIES, $formView->vars['form']->children);
    }

    #[Test]
    public function view_has_no_category_choice_element_if_there_is_exactly_one_choice(): void
    {
        $this->setUpOneCategory();

        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $this->assertArrayNotHasKey(StartRegistrationType::ELEMENT_CATEGORIES, $formView->vars['form']->children);
    }

    #[Test]
    public function view_contains_category_choice_element_if_there_is_more_than_one_choice(): void
    {
        $this->setUpTwoCategories();

        $formView = $this->factory->create(StartRegistrationType::class)->createView();
        $categoriesVars = $formView->vars['form']->children[StartRegistrationType::ELEMENT_CATEGORIES]->vars;
        $this->assertArrayHasKey('choices', $categoriesVars);

        $this->assertCount(2, $categoriesVars['choices']);
        $this->assertEquals($this->category1->getId(), $categoriesVars['choices'][0]->value);
        $this->assertEquals($this->category1->getName(), $categoriesVars['choices'][0]->label);
        $this->assertEquals($this->category2->getId(), $categoriesVars['choices'][1]->value);
        $this->assertEquals($this->category2->getName(), $categoriesVars['choices'][1]->label);
    }

    #[Test]
    public function does_not_validate_without_honeypot()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors());
        $this->assertEquals(
            HoneypotType::ERROR_MESSAGE_HONEYPOT_NOT_SUBMITTED,
            $form->getErrors()->current()->getMessage()
        );
    }

    #[Test]
    public function does_not_validate_with_filled_honeypot()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            StartRegistrationType::ELEMENT_HONEYPOT => 'http://spam.com',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors());
        $this->assertEquals(HoneypotType::ERROR_MESSAGE_HONEYPOT_FILLED, $form->getErrors()->current()->getMessage());
    }

    #[Test]
    public function does_not_validate_without_email_address()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => '',
            StartRegistrationType::ELEMENT_HONEYPOT => '',
        ]);
        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals((new NotBlank())->message, $form->getErrors(true, true)->current()->getMessage());
    }

    #[Test]
    public function does_not_validate_with_invalid_email_address()
    {
        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'this is no valid email address',
            StartRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals((new Email())->message, $form->getErrors(true, true)->current()->getMessage());
    }

    #[Test]
    public function does_not_validate_with_already_registering_email_address_if_not_enough_time_has_passed()
    {
        $veryRecentPendingOptIn = new PendingOptIn(null, new EmailAddress('webfactory@example.com', 'secret'));
        $this->pendingOptInRepository
            ->method('findByEmailAddress')
            ->willReturn($veryRecentPendingOptIn);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            StartRegistrationType::ELEMENT_HONEYPOT => '',
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

    #[Test]
    public function does_validate_with_already_registering_email_address_if_enough_time_has_passed()
    {
        $oldPendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new DateTimeImmutable('-'.(self::MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS + 1).' hour')
        );
        $this->pendingOptInRepository
            ->method('findByEmailAddress')
            ->willReturn($oldPendingOptIn);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            StartRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isValid());
    }

    #[Test]
    public function does_not_validate_if_category_choices_exist_but_none_was_selected()
    {
        $this->setUpTwoCategories();

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            StartRegistrationType::ELEMENT_CATEGORIES => [],
            StartRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertCount(1, $form->getErrors(true, true));
        $this->assertEquals(
            'You must select at least 1 choice.',
            $form->getErrors(true, true)->current()->getMessage()
        );
    }

    #[Test]
    public function provides_PendingOptIn_if_submitted_with_valid_data_without_category_choices()
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
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            StartRegistrationType::ELEMENT_HONEYPOT => '',
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEquals($pendingOptIn, $form->getData());
    }

    #[Test]
    public function provides_PendingOptIn_if_submitted_with_valid_data_and_category_choices()
    {
        $this->setUpTwoCategories();

        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [$this->category1, $this->category2]
        );
        $this->pendingOptInFactory
            ->method('fromRegistrationFormData')
            ->with(
                $this->callback(
                    function (array $formData) {
                        return \array_key_exists(StartRegistrationType::ELEMENT_EMAIL_ADDRESS, $formData)
                            && $formData[StartRegistrationType::ELEMENT_EMAIL_ADDRESS] instanceof EmailAddress
                            && 'webfactory@example.com' === (string) $formData[StartRegistrationType::ELEMENT_EMAIL_ADDRESS]->getEmailAddress()
                            && \array_key_exists(StartRegistrationType::ELEMENT_CATEGORIES, $formData)
                            && $formData[StartRegistrationType::ELEMENT_CATEGORIES] === [$this->category1, $this->category2];
                    }
                )
            )
            ->willReturn($pendingOptIn);

        $form = $this->factory->create(StartRegistrationType::class);
        $form->submit([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            StartRegistrationType::ELEMENT_CATEGORIES => [$this->category1->getId(), $this->category2->getId()],
            StartRegistrationType::ELEMENT_HONEYPOT => '',
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
                    new StartRegistrationType($this->categoryRepository, $this->pendingOptInFactory),
                    new EmailAddressType(
                        $this->blockedEmailAddressHashRepository,
                        $this->pendingOptInRepository,
                        $this->emailAddressFactory,
                        self::MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS,
                        $this->translator
                    ),
                    new HoneypotType($this->translator),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    protected function setUpOneCategory(): void
    {
        $this->category1 = new Category(1, 'Category 1');
        $this->categoryRepository->method('findVisible')->willReturn([$this->category1]);
    }

    protected function setUpTwoCategories(): void
    {
        $this->category1 = new Category(1, 'Category 1');
        $this->category2 = new Category(2, 'Category 2');
        $this->categoryRepository->method('findVisible')->willReturn([$this->category1, $this->category2]);
    }
}
