<?php

/**
 * NPI check digit (Luhn over "80840" + the first nine digits).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final class Npi
{
    public static function isValid(string $npi): bool
    {
        if (preg_match('/^\d{10}$/', $npi) !== 1) {
            return false;
        }
        $digits = '80840' . substr($npi, 0, 9);
        $sum = 0;
        $double = true;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $digit = (int) $digits[$i];
            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $double = !$double;
        }
        return (10 - $sum % 10) % 10 === (int) $npi[9];
    }

    public static function parse(MapReader $map, string $key): string
    {
        $npi = $map->string($key);
        if (!self::isValid($npi)) {
            throw SeedFileException::at($map->pathOf($key), 'not a valid NPI (10 digits with a correct check digit)');
        }
        return $npi;
    }
}
