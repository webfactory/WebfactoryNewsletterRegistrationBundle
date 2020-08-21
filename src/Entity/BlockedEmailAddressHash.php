<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity
 */
class BlockedEmailAddressHash implements BlockedEmailAddressHashInterface
{
    /**
     * @ORM\Column(type="string", nullable=false)
     * @ORM\Id
     *
     * @var string
     */
    protected $hash;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     *
     * @var \DateTimeImmutable
     */
    protected $blockDate;

    public static function fromEmailAddress(
        EmailAddress $emailAddress,
        \DateTimeImmutable $blockDate = null
    ): BlockedEmailAddressHashInterface {
        return new self($emailAddress->getHash(), $blockDate);
    }

    public function __construct(string $hash, \DateTimeImmutable $blockDate = null)
    {
        $this->hash = $hash;
        $this->blockDate = $blockDate ?? new \DateTimeImmutable();
    }
}
