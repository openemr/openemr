<?php

/**
 * Tests for AutoValueSubscriber
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Entities\EventSubscriber;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Entities\Attributes\CreatedAt;
use OpenEMR\Entities\Attributes\UpdatedAt;
use OpenEMR\Entities\Attributes\Uuid;
use OpenEMR\Entities\EventSubscriber\AutoValueSubscriber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\UuidInterface;

final class AutoValueSubscriberTest extends TestCase
{
    private EntityManagerInterface $em;
    private DateTimeImmutable $fixedTime;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->fixedTime = new DateTimeImmutable('2026-03-10 12:00:00');
    }

    private function createSubscriber(?DateTimeImmutable $time = null): AutoValueSubscriber
    {
        return new AutoValueSubscriber(new FrozenClock($time ?? $this->fixedTime));
    }

    public function testPrePersistSetsCreatedAtAndUpdatedAt(): void
    {
        $entity = new class {
            #[CreatedAt]
            public DateTimeImmutable $createdAt;

            #[UpdatedAt]
            public DateTimeImmutable $updatedAt;
        };

        $this->createSubscriber()->prePersist(new PrePersistEventArgs($entity, $this->em));

        self::assertSame($this->fixedTime, $entity->createdAt);
        self::assertSame($this->fixedTime, $entity->updatedAt);
        self::assertSame($entity->createdAt, $entity->updatedAt, 'Both timestamps should be identical');
    }

    public function testPrePersistSetsMultiplePropertiesWithSameAttribute(): void
    {
        $entity = new class {
            #[CreatedAt]
            public DateTimeImmutable $createdAt;

            #[CreatedAt]
            public DateTimeImmutable $anotherCreatedAt;
        };

        $this->createSubscriber()->prePersist(new PrePersistEventArgs($entity, $this->em));

        self::assertSame($this->fixedTime, $entity->createdAt);
        self::assertSame($this->fixedTime, $entity->anotherCreatedAt);
    }

    public function testPrePersistGeneratesUuid4(): void
    {
        $entity = new class {
            #[Uuid]
            public UuidInterface $uuid;
        };

        $this->createSubscriber()->prePersist(new PrePersistEventArgs($entity, $this->em));

        $fields = $entity->uuid->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertSame(4, $fields->getVersion());
    }

    public function testPrePersistGeneratesUniqueUuidsForMultipleProperties(): void
    {
        $entity = new class {
            #[Uuid]
            public UuidInterface $primaryUuid;

            #[Uuid]
            public UuidInterface $secondaryUuid;
        };

        $this->createSubscriber()->prePersist(new PrePersistEventArgs($entity, $this->em));

        self::assertNotEquals(
            $entity->primaryUuid->toString(),
            $entity->secondaryUuid->toString(),
            'Multiple UUID properties should receive different values'
        );
    }

    public function testPreUpdateSetsUpdatedAtProperty(): void
    {
        $entity = new class {
            #[UpdatedAt]
            public DateTimeImmutable $updatedAt;
        };

        $changeSet = [];
        $this->createSubscriber()->preUpdate(new PreUpdateEventArgs($entity, $this->em, $changeSet));

        self::assertSame($this->fixedTime, $entity->updatedAt);
    }

    public function testPreUpdateDoesNotModifyCreatedAtOrUuid(): void
    {
        $originalTime = new DateTimeImmutable('2026-03-01 10:00:00');
        $originalUuid = \Ramsey\Uuid\Uuid::uuid4();

        $entity = new class {
            #[CreatedAt]
            public DateTimeImmutable $createdAt;

            #[UpdatedAt]
            public DateTimeImmutable $updatedAt;

            #[Uuid]
            public UuidInterface $uuid;
        };
        $entity->createdAt = $originalTime;
        $entity->updatedAt = $originalTime;
        $entity->uuid = $originalUuid;

        $changeSet = [];
        $this->createSubscriber()->preUpdate(new PreUpdateEventArgs($entity, $this->em, $changeSet));

        self::assertSame($originalTime, $entity->createdAt, 'CreatedAt should not change on update');
        self::assertSame($originalUuid, $entity->uuid, 'UUID should not change on update');
        self::assertSame($this->fixedTime, $entity->updatedAt);
    }

    public function testPrePersistDoesNotModifyPropertiesWithoutAttributes(): void
    {
        $entity = new class {
            public string $name = 'original';
            public int $count = 42;
            public ?DateTimeImmutable $createdAt= null;
            public ?UuidInterface $uuid = null;
        };

        $this->createSubscriber()->prePersist(new PrePersistEventArgs($entity, $this->em));

        self::assertSame('original', $entity->name);
        self::assertSame(42, $entity->count);
        self::assertNull($entity->createdAt);
        self::assertNull($entity->uuid);
    }

    public function testPreUpdateDoesNotModifyPropertiesWithoutAttributes(): void
    {
        $entity = new class {
            public string $name = 'original';
            public int $count = 42;
            public ?DateTimeImmutable $updatedAt = null;
        };

        $changeSet = [];
        $this->createSubscriber()->preUpdate(new PreUpdateEventArgs($entity, $this->em, $changeSet));

        self::assertSame('original', $entity->name);
        self::assertSame(42, $entity->count);
        self::assertNull($entity->updatedAt);
    }

    public function testHandlesEntityWithNoProperties(): void
    {
        $entity = new class {
        };

        // Should not throw - this is the assertion
        $this->createSubscriber()->prePersist(new PrePersistEventArgs($entity, $this->em));

        $changeSet = [];
        $this->createSubscriber()->preUpdate(new PreUpdateEventArgs($entity, $this->em, $changeSet));

        $this->expectNotToPerformAssertions();
    }

    public function testMixedEntityLeavesUnmarkedPropertiesAlone(): void
    {
        $entity = new class {
            #[CreatedAt]
            public DateTimeImmutable $createdAt;

            #[UpdatedAt]
            public DateTimeImmutable $updatedAt;

            #[Uuid]
            public UuidInterface $uuid;

            public string $normalProperty = 'untouched';
            public ?DateTimeImmutable $regularDate = null;
        };

        $this->createSubscriber()->prePersist(new PrePersistEventArgs($entity, $this->em));

        self::assertSame($this->fixedTime, $entity->createdAt);
        self::assertSame($this->fixedTime, $entity->updatedAt);
        self::assertSame('untouched', $entity->normalProperty);
        self::assertNull($entity->regularDate);
    }
}
