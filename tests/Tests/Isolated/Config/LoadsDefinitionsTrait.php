<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Config;

use Closure;

trait LoadsDefinitionsTrait
{
    /**
     * @param string $file Definition file name in config/, e.g. 'database.php'
     */
    private static function definition(string $file, string $id): Closure
    {
        $definitions = require __DIR__ . '/../../../../config/' . $file;
        self::assertIsArray($definitions);
        $factory = $definitions[$id] ?? null;
        self::assertInstanceOf(Closure::class, $factory, "$id should have a factory definition");
        return $factory;
    }
}
