<?php

/**
 * The information source of a 277 transaction (loop 2100A): the entity whose statuses it carries.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

final readonly class ClaimStatusSource
{
    /**
     * @param string $entityCode          NM101, PR for a payer and AY for a clearinghouse
     * @param string $name                NM103
     * @param string $identifierQualifier NM108, what kind of ID the identifier is, e.g. PI for a
     *                                    payer ID or 46 for an electronic transmitter ID (ETIN)
     * @param string $identifier          NM109, the payer ID or the clearinghouse's ID
     */
    public function __construct(
        public string $entityCode,
        public string $name,
        public string $identifierQualifier,
        public string $identifier,
    ) {
    }

    public function kind(): ClaimStatusSourceKind
    {
        return ClaimStatusSourceKind::fromEntityCode($this->entityCode);
    }
}
