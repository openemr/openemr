<?php

/**
 * SchemaVersion - Value object for the database schema version
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

final readonly class SchemaVersion
{
    public function __construct(
        public int $major,
        public int $minor,
        public int $patch,
        public int $database,
        public int $acl,
    ) {
    }

    /**
     * @param array<string, scalar> $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            major: (int) ($row['v_major'] ?? 0),
            minor: (int) ($row['v_minor'] ?? 0),
            patch: (int) ($row['v_patch'] ?? 0),
            database: (int) ($row['v_database'] ?? 0),
            acl: (int) ($row['v_acl'] ?? 0),
        );
    }
}
