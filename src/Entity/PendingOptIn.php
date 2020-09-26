<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;

/**
 * @ ORM\Entity(repositoryClass="PendingOptInRepository")
 * @ ORM\Table(
 *     uniqueConstraints={
 *         @ ORM\UniqueConstraint(columns={"emailAddressHash"}),
 *         @ ORM\UniqueConstraint(columns={"uuid"}),
 *     }
 * )
 */
abstract class PendingOptIn implements PendingOptInInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=36, unique=true, nullable=false)
     *
     * @var string
     */
    protected $uuid;

    /**
     * @var EmailAddress
     *
     * Not ORM-mapped since we don't want to store personal data before confirmation.
     */
    protected $emailAddress;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     *
     * Hash of normalized email address.
     */
    protected $emailAddressHash;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     *
     * @var \DateTimeImmutable
     */
    protected $registrationDate;

    /**
     * @ORM\ManyToMany(targetEntity="Webfactory\NewsletterRegistrationBundle\Entity\NewsletterInterface")
     * @ORM\JoinTable(
     *     joinColumns={@ORM\JoinColumn(referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     *
     * @var Collection of NewsletterInterface
     */
    protected $newsletters;

    public static function fromRegistrationFormData(array $formData): ?PendingOptInInterface
    {
        $emailAddress = $formData[StartRegistrationType::ELEMENT_EMAIL_ADDRESS];
        if (!($emailAddress instanceof EmailAddress)) {
            return null;
        }

        return new static(
            null,
            $emailAddress,
            $formData[StartRegistrationType::ELEMENT_NEWSLETTERS] ?? []
        );
    }

    public function __construct(
        ?string $uuid,
        EmailAddress $emailAddress,
        array $newsletters = [],
        ?\DateTimeImmutable $registrationDate = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4()->toString();
        $this->emailAddress = $emailAddress;
        $this->emailAddressHash = $emailAddress->getHash();
        $this->newsletters = new ArrayCollection($newsletters);
        $this->registrationDate = $registrationDate ?: new \DateTimeImmutable();
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

    public function getNewsletters(): array
    {
        return $this->newsletters->toArray();
    }

    public function getRegistrationDate(): \DateTimeImmutable
    {
        return $this->registrationDate;
    }

    public function isOutdated(\DateTimeImmutable $threshold): bool
    {
        return $this->getRegistrationDate() < $threshold;
    }

    public function isAllowedToReceiveAnotherOptInEmail(
        \DateInterval $minimalIntervalBetweenOptInEmailsInHours,
        ?\DateTimeImmutable $now = null
    ): bool {
        $now = $now ?? new \DateTimeImmutable();

        return $this->getRegistrationDate()->add($minimalIntervalBetweenOptInEmailsInHours) < $now;
    }
}
