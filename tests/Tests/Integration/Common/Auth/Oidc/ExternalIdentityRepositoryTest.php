<?php

/**
 * Integration tests for ExternalIdentityRepository against a real database.
 *
 * Requires Docker MySQL to be running with the oidc_external_identity table.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Common\Auth\Oidc;

use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityMapping;
use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityRepository;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExternalIdentityRepository::class)]
final class ExternalIdentityRepositoryTest extends TestCase
{
    private ExternalIdentityRepository $repository;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        $this->repository = new ExternalIdentityRepository();
        $this->cleanTable();
    }

    protected function tearDown(): void
    {
        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanTable();
        }
    }

    private function cleanTable(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . ExternalIdentityRepository::TABLE_NAME . '`',
        );
    }

    public function testSaveAndFindByExternal(): void
    {
        $mapping = ExternalIdentityMapping::create(
            userId: 1,
            issuer: 'https://accounts.example.com',
            externalId: 'ext-user-1',
            email: 'user@example.com',
        );

        $this->repository->save($mapping);

        $found = $this->repository->findByExternal('https://accounts.example.com', 'ext-user-1');

        self::assertNotNull($found);
        self::assertSame(1, $found->userId);
        self::assertSame('https://accounts.example.com', $found->issuer);
        self::assertSame('ext-user-1', $found->externalId);
        self::assertSame('user@example.com', $found->email);
        self::assertNotNull($found->id);
        self::assertNotNull($found->createdAt);
    }

    public function testFindByUserId(): void
    {
        $mapping = ExternalIdentityMapping::create(2, 'https://issuer.example.com', 'ext-2');
        $this->repository->save($mapping);

        $found = $this->repository->findByUserId(2);

        self::assertNotNull($found);
        self::assertSame('https://issuer.example.com', $found->issuer);
        self::assertSame('ext-2', $found->externalId);
    }

    public function testFindByExternalReturnsNullForMissing(): void
    {
        $found = $this->repository->findByExternal('https://nonexistent.com', 'no-such-user');

        self::assertNull($found);
    }

    public function testFindByUserIdReturnsNullForMissing(): void
    {
        $found = $this->repository->findByUserId(99999);

        self::assertNull($found);
    }

    public function testSaveUpdatesExistingMapping(): void
    {
        $mapping = ExternalIdentityMapping::create(3, 'https://old-issuer.com', 'ext-3', 'old@example.com');
        $this->repository->save($mapping);

        $updated = ExternalIdentityMapping::create(3, 'https://new-issuer.com', 'ext-3-new', 'new@example.com');
        $this->repository->save($updated);

        $found = $this->repository->findByUserId(3);

        self::assertNotNull($found);
        self::assertSame('https://new-issuer.com', $found->issuer);
        self::assertSame('ext-3-new', $found->externalId);
        self::assertSame('new@example.com', $found->email);
    }

    public function testRemove(): void
    {
        $mapping = ExternalIdentityMapping::create(4, 'https://issuer.example.com', 'ext-4');
        $this->repository->save($mapping);

        $this->repository->remove(4);

        self::assertNull($this->repository->findByUserId(4));
    }

    public function testRemoveNonexistentDoesNotThrow(): void
    {
        $this->repository->remove(99999);

        // No exception — just a no-op
        self::assertTrue(true);
    }
}
