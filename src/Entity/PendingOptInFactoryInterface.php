<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Symfony\Component\Form\FormInterface;

interface PendingOptInFactoryInterface
{
    public function fromRegistrationForm(FormInterface $form): PendingOptInInterface;
}
