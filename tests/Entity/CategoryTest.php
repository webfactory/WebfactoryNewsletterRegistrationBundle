<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;

class CategoryTest extends TestCase
{
    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function can_be_constructed()
    {
        new Category('category name');
    }
}
