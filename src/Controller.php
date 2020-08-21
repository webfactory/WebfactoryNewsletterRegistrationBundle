<?php

namespace Webfactory\NewsletterRegistrationBundle;

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
use Webfactory\NewsletterRegistrationBundle\DeleteRegistration\Type as DeleteRegistrationType;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\Type as EditRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Exception\PendingOptInIsOutdatedException;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;
use Webfactory\NewsletterRegistrationBundle\ConfirmRegistration\TaskInterface as ConfirmRegistrationTaskInterface;
use Webfactory\NewsletterRegistrationBundle\DeleteRegistration\TaskInterface as DeleteRegistrationTaskInterface;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\TaskInterface as EditRegistrationTaskInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\TaskInterface as StartRegistrationTaskInterface;

class Controller
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var Environment */
    protected $twig;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var StartRegistrationTaskInterface */
    protected $startRegistrationTask;

    /** @var ConfirmRegistrationTaskInterface */
    protected $confirmRegistrationTask;

    /** @var EditRegistrationTaskInterface */
    protected $editRegistrationTask;

    /** @var DeleteRegistrationTaskInterface */
    protected $deleteRegistrationTask;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepository;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepository;

    public function __construct(
        FormFactoryInterface $formFactory,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        StartRegistrationTaskInterface $startRegistrationTask,
        ConfirmRegistrationTaskInterface $confirmRegistrationTask,
        EditRegistrationTaskInterface $editRegistrationTask,
        DeleteRegistrationTaskInterface $deleteRegistrationTask,
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

            // Usually, we would send a redirect here to prevent double posts. But we want to provide personal data
            // to the upcoming view - personal data that we do not want to save before the user confirmed their
            // registration. Hence, the downsides of double posts are dealt with in the form itself.
            return new Response(
                $this->twig->render(
                    '@WebfactoryNewsletterRegistration/StartRegistration/opt-in-email-sent.html.twig',
                    [
                        'pendingOptIn' => $pendingOptIn,
                        'optInEmail' => $optInEmail,
                    ]
                )
            );
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
                $this->twig->render('@WebfactoryNewsletterRegistration/DeleteRegistration/uuid-not-found.html.twig'),
                Response::HTTP_NOT_FOUND
            );
        }

        $this->deleteRegistrationTask->deleteRegistration($recipient);

        return new RedirectResponse(
            $this->urlGenerator->generate('newsletter-registration-start')
        );
    }
}
