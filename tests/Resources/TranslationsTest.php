<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\XliffFileLoader;

class TranslationsTest extends TestCase
{
    protected const DOMAIN = 'webfactory-newsletter-registration';

    public static function provideLabelKeysAndLocales(): iterable
    {
        foreach (['de', 'en'] as $locale) {
            yield [$locale, 'registration.label.categories'];
            yield [$locale, 'start.registration.label.email.address'];
        }
    }

    #[Test]
    #[DataProvider('provideLabelKeysAndLocales')]
    public function form_labels_are_translated(string $locale, string $key): void
    {
        $file = __DIR__.'/../../src/Resources/translations/'.self::DOMAIN.'+intl-icu.'.$locale.'.xlf';
        $catalogue = (new XliffFileLoader())->load($file, $locale, self::DOMAIN);

        $this->assertTrue($catalogue->has($key, self::DOMAIN), "Missing translation for '$key' in locale '$locale'.");
        $this->assertNotEquals($key, $catalogue->get($key, self::DOMAIN));
    }
}
