<?php

/**
 * The policy Receipts by Payer reports an insurer payment posted without a
 * payer under.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Services\Insurance\CoverageDay;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use OpenEMR\Services\Insurance\InsuranceLevel;
use OpenEMR\Services\InsuranceService;

/**
 * Invoice and ERA posting can leave an insurer payment's session without a
 * payer (ar_session.payer_id 0). Receipts by Payer (interface/reports/
 * receipts_by_method_report.php) then reports the payment under the
 * patient's policy at the payment's level in force on the date of service,
 * as InsuranceService::getEffectiveInsuranceRows() chooses it: the policy's
 * company names the payer, and its number fills the Policy column, so the
 * two come from one policy.
 */
final readonly class ReceiptPayerPolicy
{
    /**
     * @param int    $companyId    insurance_data.provider: the company that names the payer
     * @param string $policyNumber insurance_data.policy_number, '' when the policy has none
     */
    public function __construct(
        public int $companyId,
        public string $policyNumber,
    ) {
    }

    /**
     * The policy at the level in force on the date of service, or null when
     * the level has none then, or the patient or the date can't be read.
     *
     * @param mixed $pid         ar_activity.pid, as the report's row holds it
     * @param mixed $serviceDate form_encounter.date
     */
    public static function inForce(InsuranceService $insuranceService, mixed $pid, InsuranceLevel $level, mixed $serviceDate): ?self
    {
        $patientId = EffectiveInsuranceRule::storedId($pid);
        $serviceDay = CoverageDay::fromStored($serviceDate);
        if ($patientId === null || $serviceDay === null) {
            return null;
        }
        foreach ($insuranceService->getEffectiveInsuranceRows($patientId, $serviceDay) as $effective) {
            if ($effective->policy->level === $level) {
                $number = $effective->columns['policy_number'] ?? null;
                return new self($effective->policy->companyId, is_scalar($number) ? (string) $number : '');
            }
        }
        return null;
    }
}
