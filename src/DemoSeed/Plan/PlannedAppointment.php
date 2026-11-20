<?php

/**
 * A timed appointment to write.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

final readonly class PlannedAppointment
{
    /**
     * @param string $category calendar category pc_constant_id
     * @param string $status list `apptstat` option id
     */
    public function __construct(
        public int $pid,
        public string $providerKey,
        public string $facilityKey,
        public string $category,
        public \DateTimeImmutable $start,
        public int $durationMinutes,
        public string $status,
    ) {
    }
}
