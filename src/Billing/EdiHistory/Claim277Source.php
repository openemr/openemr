<?php

/**
 * Information source (2000A loop) observed in one EDI X12 277 / 277CA file.
 *
 * Holds raw facts exactly as the file states them: the NM101 entity code
 * verbatim, the name from NM103 / NM104, the identifier from NM108 / NM109,
 * and the received date from DTP*050 at that level. No code-label or
 * database lookup is applied to any value.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Community
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

final readonly class Claim277Source
{
    /**
     * @param string $entityCode  NM101 entity identifier code, verbatim (e.g. PR, AY)
     * @param ?string $name       Name assembled from NM103 / NM104
     * @param ?string $idCode     NM108 identification code qualifier
     * @param ?string $id         NM109 identification code
     * @param ?string $receivedDate  Date from DTP*050 at this level
     */
    public function __construct(
        public string $entityCode,
        public ?string $name,
        public ?string $idCode,
        public ?string $id,
        public ?string $receivedDate,
    ) {
    }
}
