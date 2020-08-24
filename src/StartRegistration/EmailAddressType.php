<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class EmailAddressType extends AbstractType implements DataMapperInterface
{
    public const ELEMENT_EMAIL_ADDRESS = 'emailAddress';

    public const ERROR_EMAIL_ADDRESS_BLOCKED = 'start.registration.email.address.blocked';
    public const ERROR_OPT_IN_EMAIL_LIMIT_REACHED = 'start.registration.opt.in.email.limit.reached';
    public const ERROR_EMAIL_ADDRESS_ALREADY_REGISTERED = 'start.registration.email.address.already.registered';

    /** @var BlockedEmailAddressHashRepositoryInterface */
    protected $blockedEmailAddressHashesRepository;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepository;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepository;

    /** @var EmailAddressFactoryInterface */
    protected $emailAddressFactory;

    /** @var int */
    protected $minimalIntervalBetweenOptInEmailsInHours;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        BlockedEmailAddressHashRepositoryInterface $blockedEmailAddressHashesRepository,
        PendingOptInRepositoryInterface $pendingOptInRepository,
        RecipientRepositoryInterface $recipientRepository,
        EmailAddressFactoryInterface $emailAddressFactory,
        int $minimalIntervalBetweenOptInEmailsInHours,
        TranslatorInterface $translator
    ) {
        $this->blockedEmailAddressHashesRepository = $blockedEmailAddressHashesRepository;
        $this->pendingOptInRepository = $pendingOptInRepository;
        $this->recipientRepository = $recipientRepository;
        $this->emailAddressFactory = $emailAddressFactory;
        $this->minimalIntervalBetweenOptInEmailsInHours = $minimalIntervalBetweenOptInEmailsInHours;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper($this);

        $that = $this;
        $builder->addModelTransformer(new CallbackTransformer(
            function (?EmailAddress $emailAddress): string {
                return $emailAddress ? $emailAddress->getEmailAddress() : '';
            },
            function (?string $emailAddressString) use ($that): ?EmailAddress {
                return $emailAddressString
                    ? $that->emailAddressFactory->fromString($emailAddressString)
                    : null;
            }
        ));
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => null,
            'required' => true,
            'compound' => false,
            'label' => 'Email address',
            'constraints' => [
                new NotBlank(),
                new Email(),
                new Callback([
                    'callback' => $this->createEmailAddressIsAllowedToReceiveOptInEmailsConstraint(),
                ]),
            ],
        ]);
    }

    protected function createEmailAddressIsAllowedToReceiveOptInEmailsConstraint(): \Closure
    {
        $that = $this;

        return function (?string $emailAddressString, ExecutionContextInterface $executionContext) use ($that) {
            if (null === $emailAddressString) {
                return;
            }

            $emailAddress = $that->emailAddressFactory->fromString($emailAddressString);
            if (null !== $this->blockedEmailAddressHashesRepository->findByEmailAddress($emailAddress)) {
                $executionContext->addViolation(
                    $this->translator->trans(
                        self::ERROR_EMAIL_ADDRESS_BLOCKED,
                        [],
                        'webfactory-newsletter-registration'
                    )
                );
            }

            $pendingOptIn = $that->pendingOptInRepository->findByEmailAddress($emailAddress);

            $dateInterval = new \DateInterval('PT'.$that->minimalIntervalBetweenOptInEmailsInHours.'H');
            if (null !== $pendingOptIn && false === $pendingOptIn->isAllowedToReceiveAnotherOptInEmail($dateInterval)) {
                $executionContext->addViolation(
                    $this->translator->trans(
                        self::ERROR_OPT_IN_EMAIL_LIMIT_REACHED,
                        ['time' => $pendingOptIn->getRegistrationDate()->add($dateInterval)->format('H:i')],
                        'webfactory-newsletter-registration'
                    )
                );
            }

            if ($that->recipientRepository->isEmailAddressAlreadyRegistered($emailAddress)) {
                $executionContext->addViolation(
                    $this->translator->trans(
                        self::ERROR_EMAIL_ADDRESS_ALREADY_REGISTERED,
                        [],
                        'webfactory-newsletter-registration'
                    )
                );
            }
        };
    }

    /**
     * @param EmailAddress|null            $viewData
     * @param FormInterface[]|\Traversable $forms
     */
    public function mapDataToForms($viewData, $forms)
    {
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof EmailAddress) {
            throw new UnexpectedTypeException($viewData, EmailAddress::class);
        }

        $forms = iterator_to_array($forms);

        /* @var FormInterface[] $forms */
        $forms[static::ELEMENT_EMAIL_ADDRESS]->setData($viewData->getEmailAddress());
    }

    public function mapFormsToData($forms, &$viewData)
    {
        $forms = iterator_to_array($forms);

        /** @var FormInterface[] $forms */
        $viewData = $this->emailAddressFactory->fromString($forms[static::ELEMENT_EMAIL_ADDRESS]->getData());
    }
}
