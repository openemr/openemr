<?php

/**
 * A table the change feed watches, and how its rows map to a FHIR resource.
 *
 * Column and table names are validated on construction because they are
 * interpolated into CREATE TRIGGER / lookup DDL, which cannot be parameterized.
 * The watched set is defined in code (see TriggerManager::defaultWatched), never
 * from user input, but the guard keeps that guarantee explicit.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ChangeFeed;

use DomainException;

final readonly class WatchedResource
{
    public function __construct(
        public string $table,
        public string $fhirResourceType,
        public string $primaryKeyColumn,
        public string $uuidColumn,
        public ?string $softDeleteColumn = null,
    ) {
        self::assertIdentifier('table', $table);
        self::assertIdentifier('fhirResourceType', $fhirResourceType);
        self::assertIdentifier('primaryKeyColumn', $primaryKeyColumn);
        self::assertIdentifier('uuidColumn', $uuidColumn);
        if ($softDeleteColumn !== null) {
            self::assertIdentifier('softDeleteColumn', $softDeleteColumn);
        }
    }

    private static function assertIdentifier(string $label, string $value): void
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value) !== 1) {
            throw new DomainException(sprintf('Invalid SQL identifier for %s: "%s"', $label, $value));
        }
    }
}
