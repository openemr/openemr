<?php

/**
 * Finds and reopens the encounters an insurance policy's payer was never
 * billed for: after a policy is added or corrected, charges already billed to
 * someone else for a date the policy covers have to go out again.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Reopen;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Insurance\CoverageDay;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use OpenEMR\Services\Insurance\InsuranceLevel;

final class PolicyReopenService
{
    /**
     * The patient's encounters with billed charges and no billed claim for the
     * company (claims.status above 1: billed, forwarded by the prior payer, or
     * denied). The balance is the one the invoice search shows: service and
     * product charges less every payment and adjustment.
     */
    private const BILLED_ELSEWHERE_QUERY = <<<'SQL'
        SELECT f.encounter, f.date, f.last_level_closed,
            ( COALESCE(( SELECT SUM(b.fee) FROM billing AS b WHERE b.pid = f.pid AND b.encounter = f.encounter
                    AND b.activity = 1 AND b.code_type != 'COPAY' ), 0)
                + COALESCE(( SELECT SUM(s.fee) FROM drug_sales AS s WHERE s.pid = f.pid
                    AND s.encounter = f.encounter ), 0)
                - COALESCE(( SELECT SUM(a.pay_amount + a.adj_amount) FROM ar_activity AS a WHERE a.pid = f.pid
                    AND a.encounter = f.encounter AND a.deleted IS NULL ), 0) ) AS balance,
            ( SELECT ic.name FROM claims AS c
                LEFT JOIN insurance_companies AS ic ON ic.id = c.payer_id AND c.payer_id > 0
                WHERE c.patient_id = f.pid AND c.encounter_id = f.encounter AND c.status > 1
                ORDER BY c.version DESC LIMIT 1 ) AS last_billed_to
        FROM form_encounter AS f
        WHERE f.pid = ?
            AND EXISTS ( SELECT 1 FROM billing AS b WHERE b.pid = f.pid AND b.encounter = f.encounter
                AND b.activity = 1 AND b.billed = 1 AND b.code_type != 'COPAY' )
            AND NOT EXISTS ( SELECT 1 FROM claims AS c WHERE c.patient_id = f.pid AND c.encounter_id = f.encounter
                AND c.payer_id = ? AND c.status > 1 )
        ORDER BY f.date DESC, f.encounter DESC
        SQL;

    /**
     * The encounters to offer for reopening after the policy was saved, most
     * recent first. An encounter is listed when
     * - the policy is the one in force at its level on the date of service,
     *   with every level before it filled (what the Billing Manager offers),
     * - its charges are billed, and no claim for it went to the policy's company,
     * - every level before the policy's is closed, so the claim would not reach
     *   this payer on its own when the prior payer's remittance is posted, and
     * - it has a balance.
     *
     * @param string $policyUuid insurance_data.uuid as text
     * @return list<ReopenCandidate> empty when the policy isn't the patient's
     */
    public function candidates(int $pid, string $policyUuid): array
    {
        return $this->find($pid, $policyUuid)['candidates'];
    }

    /**
     * Reopens the given encounters for billing to the policy's payer. Only
     * encounters candidates() lists are touched. The charges go back to
     * unbilled and the encounter to the policy's level: for a primary policy
     * this is BillingUtilities::reOpenEncounterForBilling(); for a secondary
     * or tertiary one the levels before it stay billed and closed, so the
     * Billing Manager offers this policy's payer. Statement counts restart.
     *
     * @param list<int> $encounters
     * @return list<int> the encounters reopened
     */
    public function reopen(int $pid, string $policyUuid, array $encounters): array
    {
        $found = $this->find($pid, $policyUuid);
        $priorLevel = $found['level'] - 1;

        $reopened = [];
        foreach ($found['candidates'] as $candidate) {
            if (!in_array($candidate->encounter, $encounters, true)) {
                continue;
            }
            QueryUtils::inTransaction(static function () use ($pid, $candidate, $priorLevel): void {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE billing SET billed = 0, bill_date = NULL WHERE pid = ? AND encounter = ? AND activity = 1',
                    [$pid, $candidate->encounter]
                );
                QueryUtils::sqlStatementThrowException(
                    'UPDATE form_encounter SET last_level_billed = ?, last_level_closed = ?, stmt_count = 0, '
                    . 'last_stmt_date = NULL WHERE pid = ? AND encounter = ?',
                    [$priorLevel, $priorLevel, $pid, $candidate->encounter]
                );
            });
            $reopened[] = $candidate->encounter;
        }
        return $reopened;
    }

    /**
     * The policy's level (1 to 3) and the encounters to offer for it.
     *
     * @return array{level: int<1, 3>, candidates: list<ReopenCandidate>}
     */
    private function find(int $pid, string $policyUuid): array
    {
        $policies = QueryUtils::fetchRecords('SELECT * FROM `insurance_data` WHERE `pid` = ?', [$pid]);
        $policy = self::findPolicy($policies, $policyUuid);
        if ($policy === null) {
            return ['level' => 1, 'candidates' => []];
        }

        $candidates = [];
        foreach (QueryUtils::fetchRecords(self::BILLED_ELSEWHERE_QUERY, [$pid, $policy['company']]) as $record) {
            $encounter = EffectiveInsuranceRule::storedId($record['encounter'] ?? null);
            $day = CoverageDay::fromStored($record['date'] ?? null);
            $closed = $record['last_level_closed'] ?? null;
            $balance = $record['balance'] ?? null;
            if ($encounter === null || $day === null || !is_numeric($closed) || !is_numeric($balance)) {
                continue;
            }
            $inForce = EffectiveInsuranceRule::choose($policies, $day);
            $atLevel = $inForce[$policy['level'] - 1] ?? null;
            if ($atLevel === null || $atLevel->id !== $policy['id']) {
                continue;
            }
            if ((int) $closed < $policy['level'] - 1 || round((float) $balance, 2) <= 0) {
                continue;
            }
            $lastBilledTo = $record['last_billed_to'] ?? null;
            $candidates[] = new ReopenCandidate(
                $encounter,
                $day->format('Y-m-d'),
                is_string($lastBilledTo) && $lastBilledTo !== '' ? $lastBilledTo : null,
                sprintf('%.2f', (float) $balance),
            );
        }
        return ['level' => $policy['level'], 'candidates' => $candidates];
    }

    /**
     * The policy among the patient's insurance_data rows, when it names an
     * insurance company and a level.
     *
     * @param list<array<mixed>> $policies
     * @return ?array{id: int, company: int, level: int<1, 3>}
     */
    private static function findPolicy(array $policies, string $policyUuid): ?array
    {
        if (!UuidRegistry::isValidStringUUID($policyUuid)) {
            return null;
        }
        $uuid = UuidRegistry::uuidToBytes($policyUuid);
        foreach ($policies as $row) {
            if (($row['uuid'] ?? null) !== $uuid) {
                continue;
            }
            $id = EffectiveInsuranceRule::storedId($row['id'] ?? null);
            $company = EffectiveInsuranceRule::storedId($row['provider'] ?? null);
            $type = $row['type'] ?? null;
            $level = is_string($type) ? InsuranceLevel::tryFrom($type) : null;
            if ($id === null || $company === null || $level === null) {
                return null;
            }
            return [
                'id' => $id,
                'company' => $company,
                'level' => match ($level) {
                    InsuranceLevel::Primary => 1,
                    InsuranceLevel::Secondary => 2,
                    InsuranceLevel::Tertiary => 3,
                },
            ];
        }
        return null;
    }
}
