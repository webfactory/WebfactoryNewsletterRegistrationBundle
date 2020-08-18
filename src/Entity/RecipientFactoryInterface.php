<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Symfony\Component\Form\FormInterface;

interface RecipientFactoryInterface
{
    public function fromRegistrationForm(FormInterface $form): RecipientInterface;
}
