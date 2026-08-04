<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\TypeHasCategoriesElementTrait;
use Webfactory\NewsletterRegistrationBundle\Entity\CategoryRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

class Type extends AbstractType
{
    use TypeHasCategoriesElementTrait;

    public const ELEMENT_EMAIL_ADDRESS = 'emailAddress';
    public const ELEMENT_CATEGORIES = 'categories';
    public const ELEMENT_HONEYPOT = 'url';

    protected PendingOptInFactoryInterface $pendingOptInFactory;

    public function __construct(CategoryRepositoryInterface $categoryRepository, PendingOptInFactoryInterface $pendingOptInFactory)
    {
        $this->categoryRepository = $categoryRepository;
        $this->pendingOptInFactory = $pendingOptInFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(static::ELEMENT_EMAIL_ADDRESS, EmailAddressType::class);

        $this->addCategoriesElementToForm($builder, true);

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
                    static::ELEMENT_CATEGORIES => $pendingOptIn->getCategories(),
                ];
            },
            function (array $formData) use ($that): ?PendingOptInInterface {
                return $that->pendingOptInFactory->fromRegistrationFormData($formData);
            }
        ));
    }
}
