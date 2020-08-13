<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\EntityRepository;

abstract class CategoryRepository extends EntityRepository implements CategoryRepositoryInterface
{
    public function findVisible(): array
    {
        return $this->findBy(
            ['visible' => true],
            ['name' => 'ASC']
        );
    }
}
