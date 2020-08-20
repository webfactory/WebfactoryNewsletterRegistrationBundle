<?php

namespace Webfactory\NewsletterRegistrationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\Form\DeleteRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Form\EditRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Form\StartRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Task\ConfirmRegistrationInterface;
use Webfactory\NewsletterRegistrationBundle\Task\DeleteRegistrationInterface;
use Webfactory\NewsletterRegistrationBundle\Task\EditRegistrationInterface;
use Webfactory\NewsletterRegistrationBundle\Task\StartRegistrationInterface;

abstract class RegistrationController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var Environment */
    protected $twig;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var StartRegistrationInterface */
    protected $startRegistrationTask;

    /** @var ConfirmRegistrationInterface */
    protected $confirmRegistrationTask;

    /** @var EditRegistrationInterface */
    protected $editRegistrationTask;

    /** @var DeleteRegistrationInterface */
    protected $deleteRegistrationTask;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepository;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepository;

    public function __construct(
        FormFactoryInterface $formFactory,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        StartRegistrationInterface $startRegistrationTask,
        ConfirmRegistrationInterface $confirmRegistrationTask,
        EditRegistrationInterface $editRegistrationTask,
        DeleteRegistrationInterface $deleteRegistrationTask,
        PendingOptInRepositoryInterface $pendingOptInRepository,
        RecipientRepositoryInterface $recipientRepository
    ) {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->startRegistrationTask = $startRegistrationTask;
        $this->confirmRegistrationTask = $confirmRegistrationTask;
        $this->editRegistrationTask = $editRegistrationTask;
        $this->deleteRegistrationTask = $deleteRegistrationTask;
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
            $pendingOptIn = $form->getData();
            $optInEmail = $this->startRegistrationTask->startRegistration($pendingOptIn);

            return new Response(
                $this->twig->render(
                    '@WebfactoryNewsletterRegistration/Register/opt-in-email-sent.html.twig',
                    [
                        'pendingOptIn' => $pendingOptIn,
                        'optInEmail' => $optInEmail,
                    ]
                )
            );
        }

        return new Response(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/Register/form.html.twig',
                ['registrationForm' => $form->createView()]
            )
        );
    }

    public function startRegistrationPartial(): Response
    {
        $form = $this->formFactory->createNamed('', StartRegistrationType::class);

        return new Response(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/Register/form-partial.html.twig',
                ['registrationForm' => $form->createView()]
            )
        );
    }

    /**
     * @Route("/{uuid}/{emailAddress}/", name="newsletter-registration-confirm", requirements={"uuid": "([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}", "emailAddress": ".*@.*"})
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
                $this->twig->render('@WebfactoryNewsletterRegistration/Register/opt-in-failed-due-to-unknown-uuid.html.twig')
            );
        }

        try {
            $recipient = $this->confirmRegistrationTask->confirmRegistration($pendingOptIn, $emailAddress);
        } catch (EmailAddressDoesNotMatchHashOfPendingOptInException $exception) {
            return new Response(
                $this->twig->render('@WebfactoryNewsletterRegistration/Register/opt-in-failed-due-to-email-address-not-matching.html.twig')
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
                $this->twig->render('@WebfactoryNewsletterRegistration/Edit/uuid-not-found.html.twig'),
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
                '@WebfactoryNewsletterRegistration/Edit/forms.html.twig',
                [
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
    public function deleteRegistration(string $uuid)
    {
        $recipient = $this->recipientRepository->findByUuid($uuid);
        if (null === $recipient) {
            return new Response(
                $this->twig->render('@WebfactoryNewsletterRegistration/Delete/uuid-not-found.html.twig'),
                Response::HTTP_NOT_FOUND
            );
        }

        $this->deleteRegistrationTask->deleteRegistration($recipient);

        return new RedirectResponse(
            $this->urlGenerator->generate('newsletter-registration-start')
        );
    }
}
