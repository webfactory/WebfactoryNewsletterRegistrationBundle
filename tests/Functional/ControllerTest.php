<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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
}
