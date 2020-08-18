<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Webfactory\NewsletterRegistrationBundle\Form\RegisterType;

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
     * @var string
     *
     * A non ORM-mapped field for a normalized email address.
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
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
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

    public static function fromRegistrationFormData(array $formData, string $secret): PendingOptInInterface
    {
        return new static(
            null,
            $formData[RegisterType::ELEMENT_EMAIL_ADDRESS],
            $secret,
            \array_key_exists(RegisterType::ELEMENT_NEWSLETTERS, $formData) ? $formData[RegisterType::ELEMENT_NEWSLETTERS] : []
        );
    }

    public static function normalizeEmailAddress(string $string): string
    {
        return mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
    }

    public static function hashEmailAddress(string $string, string $secret): string
    {
        return md5($secret.self::normalizeEmailAddress($string));
    }

    public function __construct(?string $uuid, string $emailAddress, string $secret, array $newsletters = [], ?\DateTime $registrationDate = null)
    {
        $this->uuid = $uuid ?: Uuid::uuid4()->toString();
        $this->emailAddress = static::normalizeEmailAddress($emailAddress);
        $this->emailAddressHash = static::hashEmailAddress($emailAddress, $secret);
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
}
