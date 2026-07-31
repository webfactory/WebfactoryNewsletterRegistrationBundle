<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Resources;

use AppBundle\Newsletter\Entity\Newsletter as NewsletterTemplate;
use AppBundle\Newsletter\Entity\NewsletterRepository as NewsletterRepositoryTemplate;
use AppBundle\Newsletter\Entity\PendingOptIn as PendingOptInTemplate;
use AppBundle\Newsletter\Entity\PendingOptInRepository as PendingOptInRepositoryTemplate;
use AppBundle\Newsletter\Entity\Recipient as RecipientTemplate;
use AppBundle\Newsletter\Entity\RecipientRepository as RecipientRepositoryTemplate;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\SchemaValidator;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\Newsletter as AbstractNewsletter;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepository as AbstractNewsletterRepository;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn as AbstractPendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepository as AbstractPendingOptInRepository;
use Webfactory\NewsletterRegistrationBundle\Entity\Recipient as AbstractRecipient;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepository as AbstractRecipientRepository;

class AppClassTemplatesTest extends TestCase
{
    private EntityManager $em;

    protected function setUp(): void
    {
        $resolveTargetEntityListener = new ResolveTargetEntityListener();
        $resolveTargetEntityListener->addResolveTargetEntity(
            NewsletterInterface::class,
            NewsletterTemplate::class,
            []
        );

        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($resolveTargetEntityListener);

        $connection = DriverManager::getConnection(
            ['driver' => 'pdo_sqlite', 'memory' => true],
        );

        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__.'/../../Resources/app-class-templates'],
            isDevMode: true,
        );
        $config->enableNativeLazyObjects(true);

        $this->em = new EntityManager($connection, $config, $eventManager);
    }

    /** @test */
    public function newsletter_template_extends_abstract_newsletter(): void
    {
        self::assertTrue(is_subclass_of(NewsletterTemplate::class, AbstractNewsletter::class));
    }

    /** @test */
    public function recipient_template_extends_abstract_recipient(): void
    {
        self::assertTrue(is_subclass_of(RecipientTemplate::class, AbstractRecipient::class));
    }

    /** @test */
    public function pending_opt_in_template_extends_abstract_pending_opt_in(): void
    {
        self::assertTrue(is_subclass_of(PendingOptInTemplate::class, AbstractPendingOptIn::class));
    }

    /** @test */
    public function newsletter_repository_template_extends_abstract_newsletter_repository(): void
    {
        self::assertTrue(is_subclass_of(NewsletterRepositoryTemplate::class, AbstractNewsletterRepository::class));
    }

    /** @test */
    public function recipient_repository_template_extends_abstract_recipient_repository(): void
    {
        self::assertTrue(is_subclass_of(RecipientRepositoryTemplate::class, AbstractRecipientRepository::class));
    }

    /** @test */
    public function pending_opt_in_repository_template_extends_abstract_pending_opt_in_repository(): void
    {
        self::assertTrue(is_subclass_of(PendingOptInRepositoryTemplate::class, AbstractPendingOptInRepository::class));
    }

    /** @test */
    public function mapping_validates_without_errors(): void
    {
        $errors = (new SchemaValidator($this->em))->validateMapping();

        self::assertSame([], $errors);
    }

    /** @test */
    public function newsletter_table_name_is_correct(): void
    {
        self::assertTrue($this->getSchema()->hasTable('wfd_newsletterNewsletter'));
    }

    /** @test */
    public function recipient_table_name_is_correct(): void
    {
        self::assertTrue($this->getSchema()->hasTable('wfd_newsletterRecipient'));
    }

    /** @test */
    public function recipient_has_unique_constraints_on_email_and_uuid(): void
    {
        $constrainedColumns = $this->getUniqueConstraintColumns('wfd_newsletterRecipient');

        self::assertContains(['email'], $constrainedColumns);
        self::assertContains(['uuid'], $constrainedColumns);
    }

    /** @test */
    public function pending_opt_in_has_unique_constraints_on_email_address_hash_and_uuid(): void
    {
        $tableName = $this->em->getClassMetadata(PendingOptInTemplate::class)->getTableName();
        $constrainedColumns = $this->getUniqueConstraintColumns($tableName);

        self::assertContains(['emailAddressHash'], $constrainedColumns);
        self::assertContains(['uuid'], $constrainedColumns);
    }

    private function getSchema(): \Doctrine\DBAL\Schema\Schema
    {
        $classes = $this->em->getMetadataFactory()->getAllMetadata();

        return (new SchemaTool($this->em))->getSchemaFromMetadata($classes);
    }

    /**
     * Returns column lists for all unique indexes/constraints on a table.
     * Doctrine emits ORM uniqueConstraints as named unique indexes on SQLite.
     */
    private function getUniqueConstraintColumns(string $tableName): array
    {
        $table = $this->getSchema()->getTable($tableName);

        $columns = array_map(
            fn ($uc) => array_values($uc->getColumns()),
            $table->getUniqueConstraints()
        );

        foreach ($table->getIndexes() as $index) {
            if ($index->isUnique() && !$index->isPrimary()) {
                $columns[] = array_values($index->getColumns());
            }
        }

        return $columns;
    }
}
