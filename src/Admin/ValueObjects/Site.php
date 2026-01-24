<?php

/**
 * Site Value Object
 *
 * Immutable DTO representing complete site information.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\ValueObjects;

class Site
{
    private function __construct(private readonly string $siteId, private readonly string $dbName, private readonly string $siteName, private readonly string $version, private readonly bool $needsSetup, private readonly string $error, private readonly bool $requiresUpgrade, private readonly string $upgradeType, private readonly bool $isCurrent)
    {
    }

    /**
     * Create a site that needs setup
     */
    public static function needsSetup(string $siteId): self
    {
        return new self(
            siteId: $siteId,
            dbName: '',
            siteName: '',
            version: 'Unknown',
            needsSetup: true,
            error: '',
            requiresUpgrade: false,
            upgradeType: '',
            isCurrent: false
        );
    }

    /**
     * Create a site with an error
     */
    public static function withError(string $siteId, string $dbName, string $error): self
    {
        return new self(
            siteId: $siteId,
            dbName: $dbName,
            siteName: '',
            version: 'Unknown',
            needsSetup: false,
            error: $error,
            requiresUpgrade: false,
            upgradeType: '',
            isCurrent: false
        );
    }

    /**
     * Create a fully configured site
     */
    public static function create(
        string $siteId,
        string $dbName,
        string $siteName,
        string $version,
        bool $requiresUpgrade,
        string $upgradeType,
        bool $isCurrent
    ): self {
        return new self(
            siteId: $siteId,
            dbName: $dbName,
            siteName: $siteName,
            version: $version,
            needsSetup: false,
            error: '',
            requiresUpgrade: $requiresUpgrade,
            upgradeType: $upgradeType,
            isCurrent: $isCurrent
        );
    }

    public function getSiteId(): string
    {
        return $this->siteId;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function getSiteName(): string
    {
        return $this->siteName;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function requiresSetup(): bool
    {
        return $this->needsSetup;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function requiresUpgrade(): bool
    {
        return $this->requiresUpgrade;
    }

    public function getUpgradeType(): string
    {
        return $this->upgradeType;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    /**
     * Convert to array for template compatibility
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'site_id' => $this->siteId,
            'db_name' => $this->dbName,
            'site_name' => $this->siteName,
            'version' => $this->version,
            'needs_setup' => $this->needsSetup,
            'error' => $this->error,
            'requires_upgrade' => $this->requiresUpgrade,
            'upgrade_type' => $this->upgradeType,
            'is_current' => $this->isCurrent,
        ];
    }
}
