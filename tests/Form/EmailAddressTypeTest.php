<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Form;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactory;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Form\EmailAddressType;

final class EmailAddressTypeTest extends TypeTestCase
{
    /** @var PendingOptInRepositoryInterface|MockObject */
    private $pendingOptInRepository;

    /** @var RecipientRepositoryInterface|MockObject */
    private $recipientRepository;

    /** @var EmailAddressFactoryInterface */
    private $emailAddressFactory;

    /** @var FormInterface */
    private $form;

    public function setUp(): void
    {
        $this->pendingOptInRepository = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->recipientRepository = $this->createMock(RecipientRepositoryInterface::class);
        $this->emailAddressFactory = new EmailAddressFactory('secret');
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
    public function does_not_validate_with_already_registering_email_address()
    {
        $this->pendingOptInRepository
            ->method('isEmailAddressAlreadyRegistered')
            ->with($this->callback(
                function (?EmailAddress $emailAddress) {
                    return !empty($emailAddress);
                }
            ))
            ->willReturn(true);

        $this->form->submit([
            'emailAddress' => 'webfactory@example.com',
        ]);

        $this->assertFalse($this->form->isValid());
        $this->assertCount(1, $this->form->getErrors(true, true));
        $this->assertEquals(
            EmailAddressType::ERROR_EMAIL_ALREADY_REGISTERING,
            $this->form->getErrors(true, true)->current()->getMessage()
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

        $this->form->submit([
            'emailAddress' => 'webfactory@example.com',
        ]);

        $this->assertFalse($this->form->isValid());
        $this->assertCount(1, $this->form->getErrors(true, true));
        $this->assertEquals(
            EmailAddressType::ERROR_EMAIL_ALREADY_REGISTERED,
            $this->form->getErrors(true, true)->current()->getMessage()
        );
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
                    new EmailAddressType($this->pendingOptInRepository,
                        $this->recipientRepository,
                        $this->emailAddressFactory
                    ),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
