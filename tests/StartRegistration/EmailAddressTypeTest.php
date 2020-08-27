<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\StartRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHash;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactory;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\EmailAddressType;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class EmailAddressTypeTest extends TypeTestCase
{
    protected const MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS = 1;

    /** @var BlockedEmailAddressHashRepositoryInterface|MockObject */
    protected $blockedEmailAddressHashRepository;

    /** @var PendingOptInRepositoryInterface|MockObject */
    protected $pendingOptInRepository;

    /** @var EmailAddressFactoryInterface */
    protected $emailAddressFactory;

    /** @var TranslatorInterface|MockObject */
    protected $translator;

    /** @var FormInterface */
    protected $form;

    public function setUp(): void
    {
        $this->blockedEmailAddressHashRepository = $this->createMock(BlockedEmailAddressHashRepositoryInterface::class);
        $this->pendingOptInRepository = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->emailAddressFactory = new EmailAddressFactory('secret');
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnArgument(0);
        parent::setUp();

        $this->form = $this->factory->createBuilder()->add('emailAddress', EmailAddressType::class)->getForm();
    }

    /**
     * @test
     */
    public function does_not_validate_without_email_address()
    {
        $this->form->submit([
            'emailAddress' => '',
        ]);

        $this->assertFalse($this->form->isValid());
        $this->assertCount(1, $this->form->getErrors(true, true));
        $this->assertEquals((new NotBlank())->message, $this->form->getErrors(true, true)->current()->getMessage());
    }

    /**
     * @test
     */
    public function does_not_validate_with_invalid_email_address()
    {
        $this->form->submit([
            'emailAddress' => 'this is no valid email address',
        ]);

        $this->assertFalse($this->form->isValid());
        $this->assertCount(1, $this->form->getErrors(true, true));
        $this->assertEquals((new Email())->message, $this->form->getErrors(true, true)->current()->getMessage());
    }

    /**
     * @test
     */
    public function does_not_validate_with_blocked_email_address()
    {
        $emailAddress = $this->emailAddressFactory->fromString('webfactory@example.com');
        $blockedEmailAddress = BlockedEmailAddressHash::fromEmailAddress($emailAddress);

        $this->blockedEmailAddressHashRepository
            ->expects($this->once())
            ->method('findByEmailAddress')
            ->willReturn($blockedEmailAddress);

        $this->form->submit([
            'emailAddress' => $emailAddress->getEmailAddress(),
        ]);

        $this->assertFalse($this->form->isValid());
        $this->assertCount(1, $this->form->getErrors(true, true));

        $this->assertEquals(
            EmailAddressType::ERROR_EMAIL_ADDRESS_BLOCKED,
            $this->form->getErrors(true, true)->current()->getMessage()
        );
    }

    /**
     * @test
     */
    public function does_not_validate_with_already_registering_email_address_if_not_enough_time_has_passed()
    {
        $veryRecentPendingOptIn = new PendingOptIn(null, $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->pendingOptInRepository
            ->method('findByEmailAddress')
            ->willReturn($veryRecentPendingOptIn);

        $this->form->submit([
            'emailAddress' => 'webfactory@example.com',
        ]);

        $this->assertFalse($this->form->isValid());
        $this->assertCount(1, $this->form->getErrors(true, true));

        // The error message is customized with a time variable, so we compare only the static text at the beginning
        $this->assertEquals(
            substr(EmailAddressType::ERROR_OPT_IN_EMAIL_LIMIT_REACHED, 0, 100),
            substr($this->form->getErrors(true, true)->current()->getMessage(), 0, 100)
        );
    }

    /**
     * @test
     */
    public function does_validate_with_already_registering_email_address_if_enough_time_has_passed()
    {
        $oldPendingOptIn = new PendingOptIn(
            null,
            $this->emailAddressFactory->fromString('webfactory@example.com'),
            [],
            new \DateTimeImmutable('-'.(self::MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS + 1).' hour')
        );
        $this->pendingOptInRepository
            ->method('findByEmailAddress')
            ->willReturn($oldPendingOptIn);

        $this->form->submit([
            'emailAddress' => 'webfactory@example.com',
        ]);

        $this->assertTrue($this->form->isValid());
        $data = $this->form->getData();
        $emailAddress = $data['emailAddress'];
        $this->assertInstanceOf(EmailAddress::class, $emailAddress);
        $this->assertEquals('webfactory@example.com', $emailAddress->getEmailAddress());
    }

    /**
     * @test
     */
    public function provides_EmailAddress_if_submitted_with_valid_data()
    {
        $this->form->submit([
            'emailAddress' => 'webfactory@example.com',
        ]);

        $this->assertTrue($this->form->isValid());
        $data = $this->form->getData();
        $emailAddress = $data['emailAddress'];
        $this->assertInstanceOf(EmailAddress::class, $emailAddress);
        $this->assertEquals('webfactory@example.com', $emailAddress->getEmailAddress());
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    new EmailAddressType(
                        $this->blockedEmailAddressHashRepository,
                        $this->pendingOptInRepository,
                        $this->emailAddressFactory,
                        self::MINIMAL_INTERVAL_BETWEEN_OPT_IN_EMAILS_IN_HOURS,
                        $this->translator
                    ),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
