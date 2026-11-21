<?php

/**
 * Changes to one imported patient's patient_data row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

final readonly class PatientUpdate
{
    /**
     * @param ?string $providerKey seed-file provider key for providerID
     * @param array<string, string> $fields patient_data columns
     */
    public function __construct(
        public int $pid,
        public ?string $providerKey,
        public array $fields,
    ) {
    }
}
