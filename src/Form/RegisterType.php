<?php

namespace Webfactory\NewsletterRegistrationBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class RegisterType extends AbstractType
{
    public const ELEMENT_EMAIL_ADDRESS = 'emailAddress';
    public const ELEMENT_NEWSLETTERS = 'newsletters';
    public const ELEMENT_HONEYPOT = 'url';
    public const ERROR_EMAIL_ALREADY_REGISTERING = 'This email address is already in the process of registering. Please see the email we\'ve send you for further details.';
    public const ERROR_EMAIL_ALREADY_REGISTERED = 'This email address is already registered.';

    /** @var NewsletterRepositoryInterface */
    private $newsletterRepository;

    /** @var PendingOptInRepositoryInterface */
    private $pendingOptInRepository;

    /** @var RecipientRepositoryInterface */
    private $recipientRepository;

    /** @var string */
    private $secret;

    public function __construct(
        NewsletterRepositoryInterface $newsletterRepository,
        PendingOptInRepositoryInterface $pendingOptInRepository,
        RecipientRepositoryInterface $recipientRepository,
        string $secret
    ) {
        $this->newsletterRepository = $newsletterRepository;
        $this->pendingOptInRepository = $pendingOptInRepository;
        $this->recipientRepository = $recipientRepository;
        $this->secret = $secret;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            self::ELEMENT_EMAIL_ADDRESS,
            EmailType::class,
            [
                'label' => 'Email address',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Callback([
                        'callback' => $this->createEmailAddressIsNotAlreadyRegisteredCallback(),
                    ]),
                ],
            ]
        );

        // add newsletter choices, if there is more than one
        $choices = $this->newsletterRepository->findVisible();
        if (\count($choices) > 1) {
            $builder->add(
                self::ELEMENT_NEWSLETTERS,
                ChoiceType::class,
                [
                    'label' => 'Newsletters',
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $choices,
                    'choice_value' => 'id',
                    'choice_label' => 'name',
                    'constraints' => [
                        new Choice(['min' => 1, 'choices' => $choices, 'multiple' => true]),
                    ],
                ]
            );
        }

        // fake field for spam protection
        $builder->add(
            self::ELEMENT_HONEYPOT,
            HoneypotType::class,
            [
                'required' => false,
            ]
        );
    }

    private function createEmailAddressIsNotAlreadyRegisteredCallback(): \Closure
    {
        $that = $this;

        return function (?string $emailAddress, ExecutionContextInterface $executionContext) use ($that) {
            if (null === $emailAddress) {
                // already handled by NotBlank above
                return;
            }

            if ($that->pendingOptInRepository->isEmailAddressHashAlreadyRegistered(PendingOptIn::hashEmailAddress($emailAddress, $this->secret))) {
                $executionContext->addViolation(self::ERROR_EMAIL_ALREADY_REGISTERING);
            }

            if ($that->recipientRepository->isEmailAddressAlreadyRegistered(PendingOptIn::normalizeEmailAddress($emailAddress))) {
                $executionContext->addViolation(self::ERROR_EMAIL_ALREADY_REGISTERED);
            }
        };
    }
}
