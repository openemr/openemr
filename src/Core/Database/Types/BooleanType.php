<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BooleanType as DoctrineBooleanType;

/**
 * Boolean type that produces BOOLEAN (TINYINT(1)) for MySQL.
 *
 * This overrides Doctrine's default which produces TINYINT without width.
 */
class BooleanType extends DoctrineBooleanType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'BOOLEAN';
    }
}
