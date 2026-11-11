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
     * Bytes from the end of a file needed to see its IEA segment.
     */
    public const TAIL_LENGTH = 256;

    /**
     * What groupCode() reports for an interchange that carries only TA1
     * acknowledgments and no functional group.
     */
    public const INTERCHANGE_ACK = 'TA1';

    /**
     * @param string $head the first HEAD_LENGTH bytes of the file
     * @return ?string GS01 of the first functional group, INTERCHANGE_ACK for an
     *                 interchange of TA1 segments only, or null when the text is not X12
     */
    public static function groupCode(string $head): ?string
    {
        if (strlen($head) <= self::ISA_LENGTH || !str_starts_with($head, 'ISA')) {
            return null;
        }
        $elementSeparator = $head[3];
        $segments = explode($head[self::ISA_LENGTH - 1], substr($head, self::ISA_LENGTH));
        $sawInterchangeAck = false;
        foreach ($segments as $segment) {
            $elements = explode($elementSeparator, ltrim($segment, "\r\n"), 3);
            // A TA1 may sit between the ISA and the first functional group.
            if ($elements[0] === 'TA1') {
                $sawInterchangeAck = true;
                continue;
            }
            if ($elements[0] === 'GS') {
                return ($elements[1] ?? '') !== '' ? $elements[1] : null;
            }
            return $elements[0] === 'IEA' && $sawInterchangeAck ? self::INTERCHANGE_ACK : null;
        }
        return null;
    }

    /**
     * Whether the file ends with a terminated IEA segment, which is what a
     * file still being written by the sender lacks.
     *
     * @param string $head the first HEAD_LENGTH bytes of the file
     * @param string $tail the last TAIL_LENGTH bytes of the file
     */
    public static function isComplete(string $head, string $tail): bool
    {
        if (strlen($head) < self::ISA_LENGTH || !str_starts_with($head, 'ISA')) {
            return false;
        }
        $segments = explode($head[self::ISA_LENGTH - 1], $tail);
        if (count($segments) < 2 || trim(array_pop($segments)) !== '') {
            return false;
        }
        return str_starts_with(ltrim(array_pop($segments), "\r\n \t"), 'IEA' . $head[3]);
    }
}
