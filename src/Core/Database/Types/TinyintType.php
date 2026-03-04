<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\SmallIntType;

/**
 * TINYINT type (smaller than SMALLINT).
 */
class TinyintType extends SmallIntType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'TINYINT';
    }
}
