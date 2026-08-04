<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\MappedSuperclass]
abstract class Recipient implements RecipientInterface
{
    /**
     * This id is used for external webfactory purposes. You may remove it and declare uuid as your primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    protected ?int $id;

    #[ORM\Column(type: 'string', length: 36, unique: true, nullable: false)]
    protected string $uuid;

    /** Normalized email address. */
    #[ORM\Column(type: 'string', name: 'email', nullable: false)]
    protected string $emailAddress;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected DateTimeImmutable $optInDate;

    /**
     * @var Collection<int, CategoryInterface>
     */
    #[ORM\ManyToMany(targetEntity: CategoryInterface::class)]
    #[ORM\JoinTable(
        joinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')]
    )]
    protected Collection $categories;

    public static function fromPendingOptIn(PendingOptInInterface $pendingOptIn): RecipientInterface
    {
        return new static(
            $pendingOptIn->getUuid(),
            $pendingOptIn->getEmailAddress(),
            $pendingOptIn->getCategories()
        );
    }

    public function __construct(
        ?string $uuid,
        EmailAddress $emailAddress,
        array $categories = [],
        ?DateTimeImmutable $optInDate = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4()->toString();
        $this->emailAddress = $emailAddress->getEmailAddress();
        $this->categories = new ArrayCollection($categories);
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

    public function getCategories(): array
    {
        return $this->categories->toArray();
    }

    public function setCategories(array $categories): void
    {
        $this->categories = new ArrayCollection($categories);
    }
}
