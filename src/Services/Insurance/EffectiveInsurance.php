<?php

/**
 * One insurance policy in force for a patient on a day, at its level.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Insurance;

use DateTimeImmutable;

final readonly class EffectiveInsurance
{
    /**
     * @param int                $id        insurance_data.id
     * @param ?string            $uuid      insurance_data.uuid as text, or null when the row has none
     * @param int                $companyId insurance_data.provider: the insurance company's id
     * @param ?DateTimeImmutable $start     the first day in force, or null when the start is blank or 0000-00-00
     *                                      (always); a partial date (2026-05-00) gives the first day the rule keeps
     * @param ?DateTimeImmutable $end       the last day in force, or null when the end is blank or 0000-00-00 (open);
     *                                      a partial date (2026-05-00) gives the last day the rule keeps (2026-04-30)
     */
    public function __construct(
        public InsuranceLevel $level,
        public int $id,
        public ?string $uuid,
        public int $companyId,
        public ?DateTimeImmutable $start,
        public ?DateTimeImmutable $end,
    ) {
    }
}
