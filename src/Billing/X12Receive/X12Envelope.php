<?php

/**
 * Reads the functional group code from the start of an X12 file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

final class X12Envelope
{
    /**
     * The ISA segment is fixed width: 106 characters, its 4th the element
     * separator and its 106th the segment terminator.
     */
    private const ISA_LENGTH = 106;

    /**
     * Bytes of a file needed to see the ISA and the GS that follows it.
     */
    public const HEAD_LENGTH = 512;

    /**
     * @param string $head the first HEAD_LENGTH bytes of the file
     * @return ?string GS01 of the first functional group, or null when the text is not X12
     */
    public static function groupCode(string $head): ?string
    {
        if (strlen($head) <= self::ISA_LENGTH || !str_starts_with($head, 'ISA')) {
            return null;
        }
        $elementSeparator = $head[3];
        $afterIsa = ltrim(substr($head, self::ISA_LENGTH), "\r\n");
        $elements = explode($elementSeparator, $afterIsa, 3);
        if (count($elements) < 3 || $elements[0] !== 'GS' || $elements[1] === '') {
            return null;
        }
        return $elements[1];
    }
}
