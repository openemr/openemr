<?php

/**
 * A unit in an HL7 v2 coded element (CE), coded as UCUM only when it is UCUM.
 *
 * The syndromic surveillance OBX-6 and the immunization RXA-7 named UCUM as the coding
 * system for any unit, free text and Drug Units titles alike. A unit UCUM doesn't accept
 * now goes in the element's text component, with no coding system. The unit is trimmed
 * first, since core's UCUM engine checks it trimmed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Hl7;

use OpenEMR\Cqm\Cql\Ucum\Ucum;
use OpenEMR\Services\FHIR\Immunization\DoseUnit;

final class Hl7Units
{
    /**
     * OBX-6: "unit^^UCUM" for a UCUM unit, "^unit" for any other, "" for none.
     */
    public static function obx6(string $unit): string
    {
        $unit = trim($unit);
        if ($unit === '') {
            return '';
        }
        return self::isUcum($unit) ? self::escape($unit) . '^^UCUM' : '^' . self::escape($unit);
    }

    /**
     * RXA-7: "code^title^UCUM^^^" for a Drug Units title with a UCUM code (mcg is ug in UCUM, grams is g),
     * "^title^^^^" for any other, "" for none. The titles are mapped as the FHIR Immunization dose maps them.
     */
    public static function rxa7(string $unit): string
    {
        $unit = trim($unit);
        if ($unit === '') {
            return '';
        }
        $code = DoseUnit::ucumFor($unit);
        return $code !== null
            ? self::escape($code) . '^' . self::escape($unit) . '^UCUM^^^'
            : '^' . self::escape($unit) . '^^^^';
    }

    /**
     * HL7 v2's escape sequences for the delimiters in a component's text, as hl7Text() in
     * global_functions.inc.php writes them: a unit of "foo^UCUM" must not become a coding system.
     */
    private static function escape(string $text): string
    {
        return str_replace(['\\', '^', '|', '~', '&', "\r"], ['\\E\\', '\\S\\', '\\F\\', '\\R\\', '\\T\\', '\\X0d\\'], $text);
    }

    private static function isUcum(string $unit): bool
    {
        return Ucum::instance()->isValid($unit);
    }
}
