<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientInterface
{
    public function getUuid(): string;

    public function getEmailAddress(): string;

    /**
     * @return CategoryInterface[]
     */
    public function getCategories(): array;

    /**
     * @param CategoryInterface[] $categories
     */
    public function setCategories(array $categories): void;
}
