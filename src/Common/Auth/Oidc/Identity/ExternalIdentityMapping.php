<?php

/**
 * Entity representing the mapping between a local OpenEMR user and an external
 * OIDC identity (issuer + subject).
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

final readonly class ExternalIdentityMapping
{
    /**
     * @param int                      $userId     Local OpenEMR user ID (FK to users.id).
     * @param string                   $issuer     OIDC issuer URL (iss claim).
     * @param string                   $externalId OIDC subject identifier (sub claim).
     * @param string|null              $email      Email at time of linking (reference only).
     * @param \DateTimeImmutable|null  $createdAt  When the mapping was created.
     * @param \DateTimeImmutable|null  $updatedAt  When the mapping was last updated.
     * @param int|null                 $id         Auto-increment primary key.
     */
    public function __construct(
        public int $userId,
        public string $issuer,
        public string $externalId,
        public ?string $email = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?int $id = null,
    ) {
        if ($userId <= 0) {
            throw new \DomainException('User ID must be positive');
        }

        if ($issuer === '') {
            throw new \DomainException('Issuer must not be empty');
        }

        if ($externalId === '') {
            throw new \DomainException('External ID must not be empty');
        }
    }

    /**
     * Create a new mapping (before persistence — no id or timestamps).
     */
    public static function create(int $userId, string $issuer, string $externalId, ?string $email = null): self
    {
        return new self(
            userId: $userId,
            issuer: $issuer,
            externalId: $externalId,
            email: $email,
        );
    }

    /**
     * Reconstruct from a database row.
     *
     * @param array<string, mixed> $row
     * @throws \Exception
     */
    public static function fromDatabaseRow(array $row): self
    {
        $rawUserId = $row['user_id'] ?? null;
        $rawIssuer = $row['issuer'] ?? null;
        $rawExternalId = $row['external_id'] ?? null;
        $rawId = $row['id'] ?? null;

        if (!is_numeric($rawUserId)) {
            throw new \DomainException('Database row missing numeric user_id');
        }

        if (!is_string($rawIssuer)) {
            throw new \DomainException('Database row missing string issuer');
        }

        if (!is_string($rawExternalId)) {
            throw new \DomainException('Database row missing string external_id');
        }

        return new self(
            userId: (int) $rawUserId,
            issuer: $rawIssuer,
            externalId: $rawExternalId,
            email: isset($row['email']) && is_string($row['email']) ? $row['email'] : null,
            createdAt: isset($row['created_at']) && is_string($row['created_at'])
                ? new \DateTimeImmutable($row['created_at'])
                : null,
            updatedAt: isset($row['updated_at']) && is_string($row['updated_at'])
                ? new \DateTimeImmutable($row['updated_at'])
                : null,
            id: is_numeric($rawId) ? (int) $rawId : null,
        );
    }
}
