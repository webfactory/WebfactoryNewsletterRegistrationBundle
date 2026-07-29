<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepository;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;
use Webfactory\NewsletterRegistrationBundle\Tests\Factory\NewsletterFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class NewsletterRepositoryTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    /** @var NewsletterRepository */
    private $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->repository = self::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Newsletter::class);
    }

    /**
     * @test
     */
    public function findVisible_returns_visible_newsletters()
    {
        NewsletterFactory::createOne();

        $newsletters = $this->repository->findVisible();

        $this->assertCount(1, $newsletters);
        $this->assertContainsOnly(Newsletter::class, $newsletters);
    }

    /**
     * @test
     */
    public function findVisible_does_not_return_invisible_newsletters()
    {
        NewsletterFactory::createOne(['visible' => false]);

        $this->assertEmpty($this->repository->findVisible());
    }

    /**
     * @test
     */
    public function findVisible_orders_by_rank()
    {
        NewsletterFactory::createOne(['name' => '1', 'rank' => 1]);
        NewsletterFactory::createOne(['name' => '3', 'rank' => 3]);
        NewsletterFactory::createOne(['name' => '2', 'rank' => 2]);

        $newsletters = $this->repository->findVisible();

        $this->assertEquals(['1', '2', '3'], array_map(fn($n) => $n->getName(), $newsletters));
    }
}
