<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\Recipient;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactory;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Form\RegisterType;

class RecipientFactoryTest extends TypeTestCase
{
    /** @var NewsletterRepositoryInterface|MockObject */
    private $newsletterRepository;

    /** @var RecipientRepositoryInterface|MockObject */
    private $recipientRepository;

    /** @var RecipientFactory */
    private $recipientFactory;

    protected function setUp(): void
    {
        $this->newsletterRepository = $this->createMock(NewsletterRepositoryInterface::class);
        $this->recipientRepository = $this->createMock(RecipientRepositoryInterface::class);
        parent::setUp();

        $this->recipientFactory = new RecipientFactory();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function testFromRegistrationForm()
    {
        // The RecipientFactory searches for a RecipientInterface implementation outside the
        // Webfactory\NewsletterRegistrationBundle namespace, so let's declare an anonymous one:
        new class(null, 'webfactory@example.com') extends Recipient {
        };

        $form = $this->factory->create(RegisterType::class);
        $form->submit([
            RegisterType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.com',
            RegisterType::ELEMENT_HONEYPOT => '',
        ]);

        $this->recipientFactory->fromRegistrationForm($form);
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([new RegisterType($this->newsletterRepository, $this->recipientRepository)], []),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
