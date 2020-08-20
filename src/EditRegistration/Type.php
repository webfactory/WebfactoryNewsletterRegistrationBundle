<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface;

class Type extends AbstractType
{
    use TypeHasNewslettersElementTrait;

    public const ELEMENT_NEWSLETTERS = 'newsletters';

    public function __construct(NewsletterRepositoryInterface $newsletterRepository)
    {
        $this->newsletterRepository = $newsletterRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addNewslettersElementToForm($builder, false);

        // We need at least one element in addition to the newsletters above, so that Symfony recognizes the form being
        // submitted even if no newsletters where chosen.
        $builder->add('hidden', HiddenType::class, ['mapped' => false]);
    }
}
