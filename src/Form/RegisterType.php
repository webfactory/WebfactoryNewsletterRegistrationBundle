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
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class RegisterType extends AbstractType
{
    public const ELEMENT_EMAIL_ADDRESS = 'emailAddress';
    public const ELEMENT_NEWSLETTERS = 'newsletters';
    public const ERROR_EMAIL_ADREADY_REGISTERED = 'This email address is already registered.';

    /** @var NewsletterRepositoryInterface */
    private $newsletterRepository;

    /** @var RecipientRepositoryInterface */
    private $recipientRepository;

    public function __construct(
        NewsletterRepositoryInterface $newsletterRepository,
        RecipientRepositoryInterface $recipientRepository
    ) {
        $this->newsletterRepository = $newsletterRepository;
        $this->recipientRepository = $recipientRepository;
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
            'url',
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

            if ($that->recipientRepository->isEmailAddressAlreadyRegistered($emailAddress)) {
                $executionContext->addViolation(self::ERROR_EMAIL_ADREADY_REGISTERED);
            }
        };
    }
}
