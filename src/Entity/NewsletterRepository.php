<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\EntityRepository;

abstract class NewsletterRepository extends EntityRepository implements NewsletterRepositoryInterface
{
    public function findVisible(): array
    {
        return $this->findBy(
            ['visible' => true],
            ['rank' => 'ASC']
        );
    }
}
