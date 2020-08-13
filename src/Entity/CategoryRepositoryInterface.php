<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface CategoryRepositoryInterface
{
    /**
     * @return CategoryInterface[]
     */
    public function findVisible(): array;
}
