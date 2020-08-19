<?php

namespace Webfactory\NewsletterRegistrationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\Form\DeleteRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Form\EditRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Form\RegisterType;
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

    /** @var StartRegistrationInterface */
    protected $startRegistrationTask;

    /** @var PendingOptInFactoryInterface */
    protected $pendingOptInFactory;

    /** @var ConfirmRegistrationInterface */
    protected $confirmRegistrationTask;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepository;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepository;

    /** @var EditRegistrationInterface */
    protected $editRegistrationTask;

    /** @var DeleteRegistrationInterface */
    protected $deleteRegistrationTask;

    public function __construct(
        FormFactoryInterface $formFactory,
        Environment $twig,
        StartRegistrationInterface $startRegistrationTask,
        PendingOptInFactoryInterface $pendingOptInFactory,
        ConfirmRegistrationInterface $confirmRegistrationTask,
        UrlGeneratorInterface $urlGenerator,
        PendingOptInRepositoryInterface $pendingOptInRepository,
        RecipientRepositoryInterface $recipientRepository,
        EditRegistrationInterface $editRegistrationTask,
        DeleteRegistrationInterface $deleteRegistrationTask
    ) {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->startRegistrationTask = $startRegistrationTask;
        $this->pendingOptInFactory = $pendingOptInFactory;
        $this->confirmRegistrationTask = $confirmRegistrationTask;
        $this->urlGenerator = $urlGenerator;
        $this->pendingOptInRepository = $pendingOptInRepository;
        $this->recipientRepository = $recipientRepository;
        $this->deleteRegistrationTask = $deleteRegistrationTask;
        $this->editRegistrationTask = $editRegistrationTask;
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
        $form = $this->formFactory->createNamed('', RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pendingOptIn = $this->pendingOptInFactory->fromRegistrationForm($form);
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
        $form = $this->formFactory->createNamed('', RegisterType::class);

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
     * @param PendingOptInInterface|null $pendingOptIn
     * @param string                     $emailAddress
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
     * @param string $uuid
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
