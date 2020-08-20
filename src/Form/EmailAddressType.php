<?php

namespace Webfactory\NewsletterRegistrationBundle\Form;

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
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class EmailAddressType extends AbstractType implements DataMapperInterface
{
    public const ELEMENT_EMAIL_ADDRESS = 'emailAddress';

    public const ERROR_EMAIL_ALREADY_REGISTERING = 'This email address is already in the process of registering. Please see the email we\'ve send you for further details.';
    public const ERROR_EMAIL_ALREADY_REGISTERED = 'This email address is already registered.';

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepository;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepository;

    /** @var EmailAddressFactoryInterface */
    protected $emailAddressFactory;

    public function __construct(
        PendingOptInRepositoryInterface $pendingOptInRepository,
        RecipientRepositoryInterface $recipientRepository,
        EmailAddressFactoryInterface $emailAddressFactory
    ) {
        $this->pendingOptInRepository = $pendingOptInRepository;
        $this->recipientRepository = $recipientRepository;
        $this->emailAddressFactory = $emailAddressFactory;
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
                    'callback' => $this->createEmailAddressIsNotAlreadyRegisteredCallback(),
                ]),
            ],
        ]);
    }

    protected function createEmailAddressIsNotAlreadyRegisteredCallback(): \Closure
    {
        $that = $this;

        return function (?string $emailAddressString, ExecutionContextInterface $executionContext) use ($that) {
            if (null === $emailAddressString) {
                return;
            }

            $emailAddress = $that->emailAddressFactory->fromString($emailAddressString);

            if ($that->pendingOptInRepository->isEmailAddressAlreadyRegistered($emailAddress)) {
                $executionContext->addViolation(self::ERROR_EMAIL_ALREADY_REGISTERING);
            }

            if ($that->recipientRepository->isEmailAddressAlreadyRegistered($emailAddress)) {
                $executionContext->addViolation(self::ERROR_EMAIL_ALREADY_REGISTERED);
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
