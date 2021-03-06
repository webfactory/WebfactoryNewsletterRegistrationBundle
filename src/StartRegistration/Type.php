<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\TypeHasNewslettersElementTrait;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

class Type extends AbstractType
{
    use TypeHasNewslettersElementTrait;

    public const ELEMENT_EMAIL_ADDRESS = 'emailAddress';
    public const ELEMENT_NEWSLETTERS = 'newsletters';
    public const ELEMENT_HONEYPOT = 'url';

    /** @var PendingOptInFactoryInterface */
    protected $pendingOptInFactory;

    public function __construct(NewsletterRepositoryInterface $newsletterRepository, PendingOptInFactoryInterface $pendingOptInFactory)
    {
        $this->newsletterRepository = $newsletterRepository;
        $this->pendingOptInFactory = $pendingOptInFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(static::ELEMENT_EMAIL_ADDRESS, EmailAddressType::class);

        $this->addNewslettersElementToForm($builder, true);

        // fake field for spam protection
        $builder->add(static::ELEMENT_HONEYPOT, HoneypotType::class);

        $that = $this;
        $builder->addModelTransformer(new CallbackTransformer(
            function (?PendingOptInInterface $pendingOptIn): array {
                if (null === $pendingOptIn) {
                    return [];
                }

                return [
                    static::ELEMENT_EMAIL_ADDRESS => (string) $pendingOptIn->getEmailAddress(),
                    static::ELEMENT_NEWSLETTERS => $pendingOptIn->getNewsletters(),
                ];
            },
            function (array $formData) use ($that): ?PendingOptInInterface {
                return $that->pendingOptInFactory->fromRegistrationFormData($formData);
            }
        ));
    }
}
