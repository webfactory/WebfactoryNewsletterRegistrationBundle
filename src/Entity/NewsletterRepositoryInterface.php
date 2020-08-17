<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface NewsletterRepositoryInterface
{
    /**
     * @return NewsletterInterface[]
     */
    public function findVisible(): array;
}
