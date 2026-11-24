<?php

/**
 * An allergy `lists` row as Ensora eRx was sent it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

/**
 * Holds the stored values of the columns the request carried, so the row is
 * marked uploaded only while they are unchanged. The outcome picks the
 * severity the request sent.
 */
final readonly class SentAllergy
{
    public function __construct(
        public int $id,
        public ?string $title,
        public int $outcome,
        public ?string $comments,
    ) {
    }
}
