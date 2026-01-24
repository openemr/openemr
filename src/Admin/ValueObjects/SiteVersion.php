<?php

/**
 * Site Version Value Object
 *
 * Represents version information from the database and provides comparison methods.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\ValueObjects;

class SiteVersion
{
    public function __construct(private readonly string $major, private readonly string $minor, private readonly string $patch, private readonly string $tag, private readonly int $realPatch, private readonly int $database, private readonly int $acl)
    {
    }

    public function getMajor(): string
    {
        return $this->major;
    }

    public function getMinor(): string
    {
        return $this->minor;
    }

    public function getPatch(): string
    {
        return $this->patch;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getRealPatch(): int
    {
        return $this->realPatch;
    }

    public function getDatabase(): int
    {
        return $this->database;
    }

    public function getAcl(): int
    {
        return $this->acl;
    }

    /**
     * Build version string for display
     */
    public function toString(): string
    {
        $patchText = '';
        if ($this->realPatch !== 0) {
            $patchText = " ({$this->realPatch})";
        }

        return "{$this->major}.{$this->minor}.{$this->patch}{$this->tag}{$patchText}";
    }

    /**
     * Check if database upgrade is required
     */
    public function requiresDatabaseUpgrade(int $expectedDatabase): bool
    {
        return $expectedDatabase !== $this->database;
    }

    /**
     * Check if ACL upgrade is required
     */
    public function requiresAclUpgrade(int $expectedAcl): bool
    {
        return $expectedAcl > $this->acl;
    }

    /**
     * Check if patch upgrade is required
     */
    public function requiresPatchUpgrade(int $expectedPatch): bool
    {
        return $expectedPatch !== $this->realPatch;
    }

    /**
     * Determine upgrade type needed, if any
     *
     * @return array{requiresUpgrade: bool, upgradeType: string, isCurrent: bool}
     */
    public function determineUpgradeStatus(int $expectedDatabase, int $expectedAcl, int $expectedPatch): array
    {
        if ($this->requiresDatabaseUpgrade($expectedDatabase)) {
            return [
                'requiresUpgrade' => true,
                'upgradeType' => 'database',
                'isCurrent' => false,
            ];
        }

        if ($this->requiresAclUpgrade($expectedAcl)) {
            return [
                'requiresUpgrade' => true,
                'upgradeType' => 'acl',
                'isCurrent' => false,
            ];
        }

        if ($this->requiresPatchUpgrade($expectedPatch)) {
            return [
                'requiresUpgrade' => true,
                'upgradeType' => 'patch',
                'isCurrent' => false,
            ];
        }

        return [
            'requiresUpgrade' => false,
            'upgradeType' => '',
            'isCurrent' => true,
        ];
    }
}
