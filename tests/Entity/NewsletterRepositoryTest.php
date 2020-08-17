<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepository;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;

class NewsletterRepositoryTest extends TestCase
{
    /** @var ORMInfrastructure */
    private $infrastructure;

    /** @var NewsletterRepository */
    private $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->infrastructure = ORMInfrastructure::createWithDependenciesFor(Newsletter::class);
        $this->repository = $this->infrastructure->getRepository(Newsletter::class);
    }

    /**
     * @test
     */
    public function findVisible_returns_visible_newsletters()
    {
        $newsletter = new Newsletter(null, 'test_newsletter');
        $this->infrastructure->import($newsletter);

        $newsletters = $this->repository->findVisible();

        $this->assertCount(1, $newsletters);
        $this->assertContainsOnly(Newsletter::class, $newsletters);
    }

    /**
     * @test
     */
    public function findVisible_does_not_return_invisible_newsletters()
    {
        $newsletter = new Newsletter(null, 'test_newsletter', 0, false);
        $this->infrastructure->import($newsletter);

        $newsletters = $this->repository->findVisible();

        $this->assertEmpty($newsletters);
    }

    /**
     * @test
     */
    public function findVisible_orders_by_rank()
    {
        $firstNewsletter = new Newsletter(null, '1', 1);
        $secondNewsletter = new Newsletter(null, '2', 2);
        $thirdNewsletter = new Newsletter(null, '3', 3);

        $this->infrastructure->import([$firstNewsletter, $thirdNewsletter, $secondNewsletter]);

        $newsletters = $this->repository->findVisible();

        $this->assertEquals([$firstNewsletter, $secondNewsletter, $thirdNewsletter], $newsletters);
    }
}
