<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlockedEmailAddressHashRepository::class)]
class BlockedEmailAddressHash implements BlockedEmailAddressHashInterface
{
    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: 'string', nullable: false)]
    protected $hash;

    /**
     * @var DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $blockDate;

    public static function fromEmailAddress(
        EmailAddress $emailAddress,
        DateTimeImmutable $blockDate = null
    ): BlockedEmailAddressHashInterface {
        return new self($emailAddress->getHash(), $blockDate);
    }

    public function __construct(string $hash, DateTimeImmutable $blockDate = null)
    {
        $this->hash = $hash;
        $this->blockDate = $blockDate ?? new DateTimeImmutable();
    }

    public function getBlockedUntilDate(DateInterval $blockDuration): DateTimeImmutable
    {
        return $this->blockDate->add($blockDuration);
    }
}
