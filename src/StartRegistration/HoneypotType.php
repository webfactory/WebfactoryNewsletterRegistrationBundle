<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Fake form type for spam protection.
 *
 * We suggest calling fields of this type "url" or similar to attract spam bots.
 */
class HoneypotType extends AbstractType
{
    /** @var LoggerInterface */
    protected $logger;

    public const ERROR_MESSAGE_HONEYPOT_NOT_SUBMITTED = 'You haven\'t submitted our spam protection field. Please try again, including it empty this time.';
    public const ERROR_MESSAGE_HONEYPOT_FILLED = 'You\'ve filled in our spam protection field. Please try again, leaving it empty this time.';

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                // validate honeypot
                $data = $event->getData();
                $form = $event->getForm();

                if ('' === $data) {
                    // Everything okay: honeypot was submitted but not filled in
                    return;
                }

                if (null === $data) {
                    $form->getParent()->addError(new FormError(self::ERROR_MESSAGE_HONEYPOT_NOT_SUBMITTED));
                    $this->logger->warning('Registration rejected: honeypot was not submitted', $this->getLogContext());

                    return;
                }

                $form->getParent()->addError(new FormError(self::ERROR_MESSAGE_HONEYPOT_FILLED));
                $this->logger->warning('Registration rejected: honeypot was filled', $this->getLogContext());
            }
        );
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'mapped' => false,
            'required' => false,
        ]);
    }

    /**
     * @return array<string,string>
     */
    protected function getLogContext(): array
    {
        $context = [];
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $context['ip'] = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        return $context;
    }
}
