<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\AppBundle\Newsletter\Entity\PendingOptInRepository")
 */
class PendingOptIn extends \Webfactory\NewsletterRegistrationBundle\Entity\Recipient
{
}
