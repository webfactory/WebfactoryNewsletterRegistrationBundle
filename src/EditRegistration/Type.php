<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\CategoryRepositoryInterface;

class Type extends AbstractType
{
    use TypeHasCategoriesElementTrait;

    public const ELEMENT_CATEGORIES = 'categories';

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addCategoriesElementToForm($builder, false);

        // We need at least one element in addition to the categories above, so that Symfony recognizes the form being
        // submitted even if no categories where chosen.
        $builder->add('hidden', HiddenType::class, ['mapped' => false]);
    }
}
