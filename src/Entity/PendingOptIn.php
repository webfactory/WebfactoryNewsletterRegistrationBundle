<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;

#[ORM\MappedSuperclass]
abstract class PendingOptIn implements PendingOptInInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true, nullable: false)]
    protected string $uuid;

    /** Not ORM-mapped since we don't want to store personal data before confirmation. */
    protected EmailAddress $emailAddress;

    /** Hash of normalized email address. */
    #[ORM\Column(type: 'string', nullable: false)]
    protected string $emailAddressHash;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected DateTimeImmutable $registrationDate;

    /**
     * @var Collection<int, CategoryInterface>
     */
    #[ORM\ManyToMany(targetEntity: CategoryInterface::class)]
    #[ORM\JoinTable(
        joinColumns: [new ORM\JoinColumn(referencedColumnName: 'uuid', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')]
    )]
    protected Collection $categories;

    public static function fromRegistrationFormData(array $formData): ?PendingOptInInterface
    {
        $emailAddress = $formData[StartRegistrationType::ELEMENT_EMAIL_ADDRESS];
        if (!($emailAddress instanceof EmailAddress)) {
            return null;
        }

        return new static(
            null,
            $emailAddress,
            $formData[StartRegistrationType::ELEMENT_CATEGORIES] ?? []
        );
    }

    public function __construct(
        ?string $uuid,
        EmailAddress $emailAddress,
        array $categories = [],
        ?DateTimeImmutable $registrationDate = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4()->toString();
        $this->emailAddress = $emailAddress;
        $this->emailAddressHash = $emailAddress->getHash();
        $this->categories = new ArrayCollection($categories);
        $this->registrationDate = $registrationDate ?: new DateTimeImmutable();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEmailAddress(): EmailAddress
    {
        return $this->emailAddress;
    }

    public function setEmailAddressIfItMatchesStoredHash(EmailAddress $emailAddress): void
    {
        if ($this->emailAddressHash !== $emailAddress->getHash()) {
            throw new EmailAddressDoesNotMatchHashOfPendingOptInException($emailAddress, $this);
        }

        $this->emailAddress = $emailAddress;
    }

    public function getCategories(): array
    {
        return $this->categories->toArray();
    }

    public function getRegistrationDate(): DateTimeImmutable
    {
        return $this->registrationDate;
    }

    public function isOutdated(DateTimeImmutable $threshold): bool
    {
        return $this->getRegistrationDate() < $threshold;
    }

    public function isAllowedToReceiveAnotherOptInEmail(
        DateInterval $minimalIntervalBetweenOptInEmailsInHours,
        ?DateTimeImmutable $now = null
    ): bool {
        $now = $now ?? new DateTimeImmutable();

        return $this->getRegistrationDate()->add($minimalIntervalBetweenOptInEmailsInHours) < $now;
    }
}
