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
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository, PendingOptInFactoryInterface $pendingOptInFactory)
    {
        $this->categoryRepository = $categoryRepository;
        $this->pendingOptInFactory = $pendingOptInFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(static::ELEMENT_EMAIL_ADDRESS, EmailAddressType::class);

        $choices = $this->categoryRepository->findVisible();

        if (\count($choices) > 1) {
            $this->addCategoriesElementToForm($builder, $choices, true);
        }

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
            function (array $formData) use ($that, $choices): ?PendingOptInInterface {
                if (!isset($formData[self::ELEMENT_CATEGORIES]) && 1 === \count($choices)) {
                    // if the field 'categories' is not in the form because you could choose only one anyway, we need to
                    // set that one category here.
                    $singleCategory = $choices[0];
                    $formData[self::ELEMENT_CATEGORIES] = [$singleCategory];
                }

                return $that->pendingOptInFactory->fromRegistrationFormData($formData);
            }
        ));
    }
}
