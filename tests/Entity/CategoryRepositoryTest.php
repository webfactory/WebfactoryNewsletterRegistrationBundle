<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Webfactory\NewsletterRegistrationBundle\Entity\CategoryRepository;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;

class CategoryRepositoryTest extends TestCase
{
    /** @var ORMInfrastructure */
    private $infrastructure;

    /** @var CategoryRepository */
    private $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->infrastructure = ORMInfrastructure::createWithDependenciesFor(Category::class);
        $this->repository = $this->infrastructure->getRepository(Category::class);
    }

    /**
     * @test
     */
    public function findVisible_returns_visible_categories()
    {
        $category = new Category('test_category');
        $this->infrastructure->import($category);

        $categories = $this->repository->findVisible();

        $this->assertCount(1, $categories);
        $this->assertContainsOnly(Category::class, $categories);
    }

    /**
     * @test
     */
    public function findVisible_does_not_return_invisible_categories()
    {
        $category = new Category('test_category', false);
        $this->infrastructure->import($category);

        $categories = $this->repository->findVisible();

        $this->assertEmpty($categories);
    }
}
