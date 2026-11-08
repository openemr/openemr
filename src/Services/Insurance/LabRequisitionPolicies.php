<?php

/**
 * The policies a paper lab requisition prints: the primary and secondary in force on the lab day.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Insurance;

final class LabRequisitionPolicies
{
    /**
     * The primary and secondary among the policies in force on the lab day
     * (InsuranceService::getEffectiveInsuranceRows() on CoverageDay::forLabOrder()), as the HL7 order bills them.
     * Each is its insurance_data row with 'name' and 'id' set to its company's, the shape the requisitions' join
     * returned. A level with nothing in force gives an empty array; a tertiary isn't printed.
     *
     * @param list<EffectiveInsuranceRow> $rows
     * @return array{primary: array<mixed>, secondary: array<mixed>}
     */
    public static function fromRows(array $rows): array
    {
        $policies = ['primary' => [], 'secondary' => []];
        foreach ($rows as $effective) {
            $row = array_merge($effective->columns, ['name' => $effective->companyName, 'id' => $effective->policy->companyId]);
            if ($effective->policy->level === InsuranceLevel::Primary) {
                $policies['primary'] = $row;
            } elseif ($effective->policy->level === InsuranceLevel::Secondary) {
                $policies['secondary'] = $row;
            }
        }
        return $policies;
    }
}
