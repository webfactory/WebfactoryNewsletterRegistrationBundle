<?php

namespace Webfactory\NewsletterRegistrationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface;

class StartRegistrationType extends AbstractType
{
    use HasNewslettersElementTrait;

    public const ELEMENT_EMAIL_ADDRESS = 'emailAddress';
    public const ELEMENT_NEWSLETTERS = 'newsletters';
    public const ELEMENT_HONEYPOT = 'url';

    public function __construct(NewsletterRepositoryInterface $newsletterRepository)
    {
        $this->newsletterRepository = $newsletterRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(static::ELEMENT_EMAIL_ADDRESS, EmailAddressType::class);

        $this->addNewslettersElementToForm($builder, true);

        // fake field for spam protection
        $builder->add(static::ELEMENT_HONEYPOT, HoneypotType::class);
    }
}
