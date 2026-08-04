<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Factory\CategoryFactory;
use Webfactory\NewsletterRegistrationBundle\Tests\Factory\PendingOptInFactory;
use Webfactory\NewsletterRegistrationBundle\Tests\Factory\RecipientFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    #[Test]
    public function start_registration_route_renders_the_registration_form(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    #[Test]
    public function edit_registration_route_returns_not_found_for_unknown_uuid(): void
    {
        $client = static::createClient();
        $client->request('GET', '/a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11/');

        self::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function confirm_registration_sets_success_flash(): void
    {
        $client = static::createClient();
        $emailAddress = 'confirm@example.com';
        $pendingOptIn = PendingOptInFactory::createOne([
            'emailAddress' => new EmailAddress($emailAddress, 'test-secret'),
        ]);

        $client->request('GET', sprintf('/%s/%s/', $pendingOptIn->getUuid(), $emailAddress));
        $client->followRedirect();

        self::assertSelectorTextContains('.flash-success', 'Your newsletter registration is now active.');
    }

    #[Test]
    public function edit_registration_with_no_category_selected_sets_success_flash(): void
    {
        $client = static::createClient();
        CategoryFactory::createMany(2);
        $recipient = RecipientFactory::createOne();

        $crawler = $client->request('GET', sprintf('/%s/', $recipient->getUuid()));
        $form = $crawler->selectButton("Change newsletter categories you're subscribed to")->form();
        $client->submit($form);

        self::assertSelectorTextContains(
            '.flash-success',
            'All your newsletter subscriptions have been deleted, but your registration data'
            .' (like your email address) is still saved in our database. If you would like to'
            .' delete that data too, please delete your registration with the button below.'
        );
    }

    #[Test]
    public function edit_registration_with_category_selected_sets_success_flash(): void
    {
        $client = static::createClient();
        $categories = CategoryFactory::createMany(2);
        $recipient = RecipientFactory::createOne();

        $crawler = $client->request('GET', sprintf('/%s/', $recipient->getUuid()));
        $form = $crawler->selectButton("Change newsletter categories you're subscribed to")->form();
        $client->submit($form, ['categories' => [$categories[0]->getId()]]);

        self::assertSelectorTextContains('.flash-success', 'Your newsletter registration was updated.');
    }

    #[Test]
    public function delete_registration_sets_success_flash(): void
    {
        $client = static::createClient();
        $recipient = RecipientFactory::createOne();

        $client->request('POST', sprintf('/%s/delete/', $recipient->getUuid()));
        $client->followRedirect();

        self::assertSelectorTextContains(
            '.flash-success',
            'You are unsubscribed from all newsletters and your registration data has been deleted.'
        );
    }
}
