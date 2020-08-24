<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\AppBundle\Newsletter\Entity\RecipientRepository")
 * @ORM\Table(name="wfd_newsletterRecipient", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="email_unique",columns={"email"}),
 *     @ORM\UniqueConstraint(name="uuid_unique",columns={"uuid"}),
 * })
 */
class Recipient extends \Webfactory\NewsletterRegistrationBundle\Entity\Recipient
{
}
