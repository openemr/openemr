<?php

/**
 * Who reported a claim status in a 277: the payer, or a clearinghouse on the way to it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

enum ClaimStatusSourceKind
{
    case Payer;
    case Clearinghouse;
    case Other;

    /**
     * @param string $entityCode NM101 of the information source (loop 2100A)
     */
    public static function fromEntityCode(string $entityCode): self
    {
        return match ($entityCode) {
            'PR' => self::Payer,
            'AY' => self::Clearinghouse,
            default => self::Other,
        };
    }
}
