<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\CategoryRepository;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;
use Webfactory\NewsletterRegistrationBundle\Tests\Factory\CategoryFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CategoryRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private CategoryRepository $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->repository = self::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Category::class);
    }

    #[Test]
    public function findVisible_returns_visible_categories()
    {
        CategoryFactory::createOne();

        $categories = $this->repository->findVisible();

        $this->assertCount(1, $categories);
        $this->assertContainsOnly(Category::class, $categories);
    }

    #[Test]
    public function findVisible_does_not_return_invisible_categories()
    {
        CategoryFactory::createOne(['visible' => false]);

        $this->assertEmpty($this->repository->findVisible());
    }

    #[Test]
    public function findVisible_orders_by_rank()
    {
        CategoryFactory::createOne(['name' => '1', 'rank' => 1]);
        CategoryFactory::createOne(['name' => '3', 'rank' => 3]);
        CategoryFactory::createOne(['name' => '2', 'rank' => 2]);

        $categories = $this->repository->findVisible();

        $this->assertEquals(['1', '2', '3'], array_map(fn ($c) => $c->getName(), $categories));
    }
}
