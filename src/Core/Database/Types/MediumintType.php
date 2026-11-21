<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

/**
 * MEDIUMINT type (smaller than INTEGER, larger than SMALLINT).
 */
class MediumintType extends IntegerType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $sql = 'MEDIUMINT';
        if (!empty($column['unsigned'])) {
            $sql .= ' UNSIGNED';
        }
        return $sql;
    }
}
