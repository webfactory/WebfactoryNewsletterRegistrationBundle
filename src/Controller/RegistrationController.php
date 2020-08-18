<?php

namespace Webfactory\NewsletterRegistrationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Form\RegisterType;
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

    public function __construct(
        FormFactoryInterface $formFactory,
        Environment $twig,
        StartRegistrationInterface $startRegistrationTask,
        PendingOptInFactoryInterface $pendingOptInFactory
    ) {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->startRegistrationTask = $startRegistrationTask;
        $this->pendingOptInFactory = $pendingOptInFactory;
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
     * @Route("/activate/", name="newsletter-registration-activate")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function activateRegistration(Request $request): Response
    {
        return new Response();
    }

    /**
     * @Route("/edit/", name="newsletter-registration-edit")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editRegistration(Request $request): Response
    {
        return new Response();
    }
}
