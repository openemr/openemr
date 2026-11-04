<?php

/**
 * A policy in force on a day with the columns callers show or send: its
 * insurance_data row and its company's name.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Insurance;

final readonly class EffectiveInsuranceRow
{
    /**
     * @param EffectiveInsurance $policy      the rule's choice: level, ids and dates
     * @param ?string            $companyName insurance_companies.name for the policy's company, or null when
     *                                        the company has no row or no name
     * @param array<mixed>       $columns     every insurance_data column as stored (plan_name, policy_number,
     *                                        group_number, the subscriber_* columns, copay, ...)
     */
    public function __construct(
        public EffectiveInsurance $policy,
        public ?string $companyName,
        public array $columns,
    ) {
    }
}
