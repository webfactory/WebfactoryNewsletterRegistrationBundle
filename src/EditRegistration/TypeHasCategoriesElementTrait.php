<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;

trait TypeHasCategoriesElementTrait
{
    protected function addCategoriesElementToForm(FormBuilderInterface $builder, array $choices, bool $recipientHasToChooseAtLeastOne): void
    {
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
