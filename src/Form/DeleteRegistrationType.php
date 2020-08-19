<?php

namespace Webfactory\NewsletterRegistrationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

class DeleteRegistrationType extends AbstractType
{
    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // This type has no fields of it's own.
        // It exists to be able to use Symfony's CSRF protection and setting some default options.

        /** @var RecipientInterface $recipient */
        $recipient = $options['recipient'];
        $builder->setAction(
            $this->urlGenerator->generate('newsletter-registration-delete', ['uuid' => $recipient->getUuid()])
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults(['method' => 'post'])
            ->setRequired('recipient');
    }
}
