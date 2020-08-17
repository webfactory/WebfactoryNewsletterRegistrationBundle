<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ ORM\Entity()
 * @ ORM\Table(
 *     name="wfd_newsletterRecipient",
 *     uniqueConstraints={
 *         @ ORM\UniqueConstraint(name="email_unique",columns={"email"}),
 *         @ ORM\UniqueConstraint(name="uuid_unique",columns={"uuid"}),
 *     }
 * )
 */
abstract class Recipient implements RecipientInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int|null
     *
     * This id is used for external webfactory purposes. You may remove it and declare uuid as your primary key.
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=36, unique=true, nullable=false)
     *
     * @var string
     */
    protected $uuid;

    /**
     * @ORM\Column(type="string", name="email", nullable=false)
     *
     * @var string
     *
     * Normalized email address.
     */
    protected $emailAddress;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $registrationDate;

    /**
     * @ORM\ManyToMany(targetEntity="Webfactory\NewsletterRegistrationBundle\Entity\NewsletterInterface")
     * @ORM\JoinTable(name="wfd_newsletterSubscription",
     *      joinColumns={@ORM\JoinColumn(name="recipient_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="newsletter_id", referencedColumnName="id")}
     * )
     *
     * @var Collection of NewsletterInterface
     */
    protected $newsletters;

    public function __construct(?string $uuid, string $emailAdress, array $newsletters = [], ?\DateTime $registrationDate = null)
    {
        $this->uuid = $uuid ?: Uuid::uuid4()->toString();
        $this->emailAddress = $this->normalize($emailAdress);
        $this->newsletters = new ArrayCollection($newsletters);
        $this->registrationDate = $registrationDate ?: new \DateTime();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getRegistrationDate(): \DateTime
    {
        return $this->registrationDate;
    }

    public function getNewsletters(): array
    {
        return $this->newsletters->toArray();
    }

    public function setNewsletters(array $newsletters): void
    {
        $this->newsletters = new ArrayCollection($newsletters);
    }

    protected function normalize(string $string): string
    {
        return mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
    }
}
