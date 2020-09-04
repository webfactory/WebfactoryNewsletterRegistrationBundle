<?php

namespace Webfactory\NewsletterRegistrationBundle;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\BlockEmails\TaskInterface as BlockEmailsTaskInterface;
use Webfactory\NewsletterRegistrationBundle\ConfirmRegistration\TaskInterface as ConfirmRegistrationTaskInterface;
use Webfactory\NewsletterRegistrationBundle\DeleteRegistration\TaskInterface as DeleteRegistrationTaskInterface;
use Webfactory\NewsletterRegistrationBundle\DeleteRegistration\Type as DeleteRegistrationType;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\TaskInterface as EditRegistrationTaskInterface;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\Type as EditRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\Exception\PendingOptInIsOutdatedException;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\HandleRegistrationSubmissionTaskInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;

class Controller
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var Environment */
    protected $twig;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var HandleRegistrationSubmissionTaskInterface */
    protected $handleRegistrationSubmissionTask;

    /** @var ConfirmRegistrationTaskInterface */
    protected $confirmRegistrationTask;

    /** @var EditRegistrationTaskInterface */
    protected $editRegistrationTask;

    /** @var DeleteRegistrationTaskInterface */
    protected $deleteRegistrationTask;

    /** @var BlockEmailsTaskInterface */
    protected $blockEmailsTask;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepository;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepository;

    public function __construct(
        FormFactoryInterface $formFactory,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        HandleRegistrationSubmissionTaskInterface $handleRegistrationSubmissionTask,
        ConfirmRegistrationTaskInterface $confirmRegistrationTask,
        EditRegistrationTaskInterface $editRegistrationTask,
        DeleteRegistrationTaskInterface $deleteRegistrationTask,
        BlockEmailsTaskInterface $blockEmailsTask,
        PendingOptInRepositoryInterface $pendingOptInRepository,
        RecipientRepositoryInterface $recipientRepository
    ) {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->handleRegistrationSubmissionTask = $handleRegistrationSubmissionTask;
        $this->confirmRegistrationTask = $confirmRegistrationTask;
        $this->editRegistrationTask = $editRegistrationTask;
        $this->deleteRegistrationTask = $deleteRegistrationTask;
        $this->blockEmailsTask = $blockEmailsTask;
        $this->pendingOptInRepository = $pendingOptInRepository;
        $this->recipientRepository = $recipientRepository;
    }

    /**
     * @Route("/", name="newsletter-registration-start")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function startRegistration(Request $request): Response
    {
        $form = $this->formFactory->createNamed('', StartRegistrationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PendingOptInInterface $pendingOptIn */
            $pendingOptIn = $form->getData();

            return $this->handleRegistrationSubmissionTask->handleRegistrationSubmission($pendingOptIn);
        }

        return new Response(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/StartRegistration/form.html.twig',
                ['registrationForm' => $form->createView()]
            )
        );
    }

    public function startRegistrationPartial(): Response
    {
        $form = $this->formFactory->createNamed('', StartRegistrationType::class);

        return new Response(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/StartRegistration/form-partial.html.twig',
                ['registrationForm' => $form->createView()]
            )
        );
    }

    /**
     * @Route("/{uuid}/{emailAddress}/", name="newsletter-registration-confirm", requirements={"uuid": "([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}", "emailAddress": ".*@((?!\/).)*"})
     *
     * @param string $uuid
     * @param string $emailAddress
     *
     * @return Response
     */
    public function confirmRegistration(string $uuid, string $emailAddress): Response
    {
        $pendingOptIn = $this->pendingOptInRepository->findByUuid($uuid);
        if (null === $pendingOptIn) {
            return new Response(
                $this->twig->render('@WebfactoryNewsletterRegistration/StartRegistration/opt-in-failed-due-to-unknown-uuid.html.twig')
            );
        }

        try {
            $recipient = $this->confirmRegistrationTask->confirmRegistration($pendingOptIn, $emailAddress);
        } catch (PendingOptInIsOutdatedException $exception) {
            return new Response(
                $this->twig->render('@WebfactoryNewsletterRegistration/StartRegistration/opt-in-failed-due-to-unknown-uuid.html.twig')
            );
        } catch (EmailAddressDoesNotMatchHashOfPendingOptInException $exception) {
            return new Response(
                $this->twig->render('@WebfactoryNewsletterRegistration/StartRegistration/opt-in-failed-due-to-email-address-not-matching.html.twig')
            );
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('newsletter-registration-edit', ['uuid' => $recipient->getUuid()])
        );
    }

    /**
     * @Route("/{uuid}/", name="newsletter-registration-edit", requirements={"uuid": "([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}"})
     *
     * @param string  $uuid
     * @param Request $request
     *
     * @return Response
     */
    public function editRegistration(string $uuid, Request $request): Response
    {
        $recipient = $this->recipientRepository->findByUuid($uuid);
        if (null === $recipient) {
            return new Response(
                $this->twig->render('@WebfactoryNewsletterRegistration/EditRegistration/uuid-not-found.html.twig'),
                Response::HTTP_NOT_FOUND
            );
        }

        $editForm = $this->formFactory->createNamed(
            '',
            EditRegistrationType::class,
            $recipient
        );
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->editRegistrationTask->editRegistration($recipient);
        }

        $deleteForm = $this->formFactory->createNamed(
            '',
            DeleteRegistrationType::class,
            null,
            ['recipient' => $recipient]
        );

        return new Response(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/EditRegistration/forms.html.twig',
                [
                    'recipient' => $recipient,
                    'editForm' => $editForm->createView(),
                    'deleteForm' => $deleteForm->createView(),
                ]
            )
        );
    }

    /**
     * @Route("/{uuid}/delete/", name="newsletter-registration-delete", methods={"POST"}, requirements={"uuid": "([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}"})
     *
     * @param string $uuid
     *
     * @return RedirectResponse|Response
     */
    public function deleteRegistration(string $uuid): Response
    {
        $recipient = $this->recipientRepository->findByUuid($uuid);
        if (null === $recipient) {
            return new Response(
                $this->twig->render('@WebfactoryNewsletterRegistration/DeleteRegistration/uuid-not-found.html.twig'),
                Response::HTTP_NOT_FOUND
            );
        }

        $this->deleteRegistrationTask->deleteRegistration($recipient);

        return new RedirectResponse(
            $this->urlGenerator->generate('newsletter-registration-start')
        );
    }

    /**
     * @Route("/{uuid}/{emailAddress}/block/", name="newsletter-registration-block-emails", requirements={"uuid": "([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}", "emailAddress": ".*@.*"})
     *
     * @param string $uuid
     * @param string $emailAddress
     *
     * @return RedirectResponse|Response
     */
    public function blockEmails(string $uuid, string $emailAddress): Response
    {
        $pendingOptIn = $this->pendingOptInRepository->findByUuid($uuid);
        if (null === $pendingOptIn) {
            return new Response(
                $this->twig->render(
                    '@WebfactoryNewsletterRegistration/BlockEmails/block-failed-due-to-unknown-uuid.html.twig',
                    ['timeLimitForOptInInHours' => $this->confirmRegistrationTask->getTimeLimitForOptInInHours()]
                )
            );
        }

        try {
            $this->blockEmailsTask->blockEmailsFor($pendingOptIn, $emailAddress);
        } catch (EmailAddressDoesNotMatchHashOfPendingOptInException $exception) {
            return new Response(
                $this->twig->render(
                    '@WebfactoryNewsletterRegistration/BlockEmails/block-failed-due-to-email-address-not-matching.html.twig'
                )
            );
        }

        return new Response(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/BlockEmails/emails-blocked.html.twig',
                ['blockDurationInDays' => $this->blockEmailsTask->getBlockDurationInDays()]
            )
        );
    }
}
