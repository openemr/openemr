<?php

/**
 * One dated fact in an encounter's claim filing history.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

/**
 * A fact the files state: billed, acknowledged, status, remittance or posted (#15530 step 3).
 * The date is Y-m-d, or '' when the files give none; nothing is guessed.
 */
final readonly class EncounterEvent
{
    public function __construct(
        public string $date,
        public string $kind,
        public string $title,
        public string $detail = '',
        public string $fileName = '',
        public string $reference = '',
        public string $dedupeKey = '',
    ) {
    }
}