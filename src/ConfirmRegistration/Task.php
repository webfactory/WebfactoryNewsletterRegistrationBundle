<?php

namespace Webfactory\NewsletterRegistrationBundle\ConfirmRegistration;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\Exception\PendingOptInIsOutdatedException;

class Task implements TaskInterface
{
    protected PendingOptInRepositoryInterface $pendingOptInRepo;
    protected int $timeLimitForOptInInHours;
    protected EmailAddressFactoryInterface $emailAddressFactory;
    protected RecipientFactoryInterface $recipientFactory;
    protected RecipientRepositoryInterface $recipientRepo;
    protected RequestStack $requestStack;
    protected TranslatorInterface $translator;

    public function __construct(
        PendingOptInRepositoryInterface $pendingOptInRepo,
        int $timeLimitForOptInInHours,
        EmailAddressFactoryInterface $emailAddressFactory,
        RecipientFactoryInterface $recipientFactory,
        RecipientRepositoryInterface $recipientRepo,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->pendingOptInRepo = $pendingOptInRepo;
        $this->timeLimitForOptInInHours = $timeLimitForOptInInHours;
        $this->emailAddressFactory = $emailAddressFactory;
        $this->recipientFactory = $recipientFactory;
        $this->recipientRepo = $recipientRepo;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    /**
     * @throws EmailAddressDoesNotMatchHashOfPendingOptInException
     * @throws PendingOptInIsOutdatedException
     */
    public function confirmRegistration(
        PendingOptInInterface $pendingOptIn,
        string $emailAddressString
    ): RecipientInterface {
        $thresholdDate = new DateTimeImmutable('-'.$this->timeLimitForOptInInHours.' hour');
        if ($pendingOptIn->isOutdated($thresholdDate)) {
            throw new PendingOptInIsOutdatedException($pendingOptIn);
        }

        $emailAddress = $this->emailAddressFactory->fromString($emailAddressString);
        $pendingOptIn->setEmailAddressIfItMatchesStoredHash($emailAddress);

        $recipient = $this->recipientFactory->fromPendingOptIn($pendingOptIn);
        $this->recipientRepo->save($recipient);
        $this->pendingOptInRepo->remove($pendingOptIn);

        $session = $this->requestStack->getSession();
        \assert($session instanceof FlashBagAwareSessionInterface);
        $session->getFlashBag()->add(
            'success',
            $this->translator->trans('confirm.registration.complete', [], 'webfactory-newsletter-registration')
        );

        return $recipient;
    }

    public function getTimeLimitForOptInInHours(): int
    {
        return $this->timeLimitForOptInInHours;
    }
}
