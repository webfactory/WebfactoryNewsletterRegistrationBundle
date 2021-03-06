<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface;

trait TypeHasNewslettersElementTrait
{
    /** @var NewsletterRepositoryInterface */
    protected $newsletterRepository;

    protected function addNewslettersElementToForm(FormBuilderInterface $builder, bool $recipientHasToChooseAtLeastOne)
    {
        // add newsletter choices, if there is more than one
        $choices = $this->newsletterRepository->findVisible();
        if (\count($choices) < 2) {
            return;
        }

        $constraints = [];
        if (true === $recipientHasToChooseAtLeastOne) {
            $constraints[] = new Choice(['min' => 1, 'choices' => $choices, 'multiple' => true]);
        }

        $builder->add(
            self::ELEMENT_NEWSLETTERS,
            ChoiceType::class,
            [
                'label' => 'Newsletters',
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
