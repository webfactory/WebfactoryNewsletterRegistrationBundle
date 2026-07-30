<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ ORM\Entity()
 * @ ORM\Table(
 *     name="wfd_newsletterRecipient",
 *     uniqueConstraints={
 *         @ ORM\UniqueConstraint(columns={"email"}),
 *         @ ORM\UniqueConstraint(columns={"uuid"}),
 *     }
 * )
 */
abstract class Recipient implements RecipientInterface
{
    /**
     * @var int|null
     *
     * This id is used for external webfactory purposes. You may remove it and declare uuid as your primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 36, unique: true, nullable: false)]
    protected $uuid;

    /**
     * @var string
     *
     * Normalized email address.
     */
    #[ORM\Column(type: 'string', name: 'email', nullable: false)]
    protected $emailAddress;

    /**
     * @var DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $optInDate;

    /**
     * @var Collection of NewsletterInterface
     */
    #[ORM\ManyToMany(targetEntity: NewsletterInterface::class)]
    #[ORM\JoinTable(
        joinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')]
    )]
    protected $newsletters;

    public static function fromPendingOptIn(PendingOptInInterface $pendingOptIn): RecipientInterface
    {
        return new static(
            $pendingOptIn->getUuid(),
            $pendingOptIn->getEmailAddress(),
            $pendingOptIn->getNewsletters()
        );
    }

    public function __construct(
        ?string $uuid,
        EmailAddress $emailAddress,
        array $newsletters = [],
        ?DateTimeImmutable $optInDate = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4()->toString();
        $this->emailAddress = $emailAddress->getEmailAddress();
        $this->newsletters = new ArrayCollection($newsletters);
        $this->optInDate = $optInDate ?: new DateTimeImmutable();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEmailAddress(): EmailAddress
    {
        return new EmailAddress($this->emailAddress, null);
    }

    public function getOptInDate(): DateTimeImmutable
    {
        return $this->optInDate;
    }

    public function getNewsletters(): array
    {
        return $this->newsletters->toArray();
    }

    public function setNewsletters(array $newsletters): void
    {
        $this->newsletters = new ArrayCollection($newsletters);
    }
}
