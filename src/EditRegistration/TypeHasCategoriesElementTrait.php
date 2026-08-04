<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Webfactory\NewsletterRegistrationBundle\Entity\CategoryRepositoryInterface;

trait TypeHasCategoriesElementTrait
{
    protected CategoryRepositoryInterface $categoryRepository;

    protected function addCategoriesElementToForm(FormBuilderInterface $builder, bool $recipientHasToChooseAtLeastOne)
    {
        // add category choices, if there is more than one
        $choices = $this->categoryRepository->findVisible();
        if (\count($choices) < 2) {
            return;
        }

        $constraints = [];
        if (true === $recipientHasToChooseAtLeastOne) {
            $constraints[] = new Choice(choices: $choices, multiple: true, min: 1);
        }

        $builder->add(
            self::ELEMENT_CATEGORIES,
            ChoiceType::class,
            [
                'label' => 'Categories',
                'multiple' => true,
                'expanded' => true,
                'choices' => $choices,
                'choice_value' => 'id',
                'choice_label' => 'name',
                'constraints' => $constraints,
            ]
        );
    }
}
