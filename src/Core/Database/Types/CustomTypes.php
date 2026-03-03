<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Database\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * Custom database types for OpenEMR.
 *
 * Use these constants instead of string literals when adding columns.
 */
final class CustomTypes
{
    public const TIMESTAMP = 'timestamp';

    /**
     * Register custom types and override built-in types.
     *
     * Call this before running migrations.
     */
    public static function register(): void
    {
        // Override built-in boolean to produce BOOLEAN (TINYINT(1))
        Type::overrideType(Types::BOOLEAN, BooleanType::class);

        // Add timestamp as a new type
        Type::addType(self::TIMESTAMP, TimestampType::class);
    }
}
