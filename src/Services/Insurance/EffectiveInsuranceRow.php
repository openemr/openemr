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

    /**
     * The policies choose() returned, in its order, each with its own row and
     * its company's name: the join InsuranceService::getEffectiveInsuranceRows()
     * makes. Ids are read as the rule reads them (EffectiveInsuranceRule::
     * storedId()), so a read that returns them as digit strings loses no row.
     * A policy whose row isn't among the rows is left out.
     *
     * @param list<EffectiveInsurance> $policies
     * @param list<array<mixed>>       $rows      the patient's insurance_data rows
     * @param list<array<mixed>>       $companies insurance_companies rows, each with id and name
     * @return list<self>
     */
    public static function forPolicies(array $policies, array $rows, array $companies): array
    {
        $rowsById = [];
        foreach ($rows as $row) {
            $id = EffectiveInsuranceRule::storedId($row['id'] ?? null);
            if ($id !== null) {
                $rowsById[$id] = $row;
            }
        }
        $names = [];
        foreach ($companies as $company) {
            $id = EffectiveInsuranceRule::storedId($company['id'] ?? null);
            $name = $company['name'] ?? null;
            if ($id !== null && is_string($name)) {
                $names[$id] = $name;
            }
        }
        $result = [];
        foreach ($policies as $policy) {
            if (isset($rowsById[$policy->id])) {
                $result[] = new self($policy, $names[$policy->companyId] ?? null, $rowsById[$policy->id]);
            }
        }
        return $result;
    }
}
