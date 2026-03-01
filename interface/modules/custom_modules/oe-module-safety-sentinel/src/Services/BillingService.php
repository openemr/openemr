<?php

/**
 * BillingService — writes ICD-10 and CPT billing entries for a finalized encounter.
 *
 * Inserts rows into OpenEMR's native `billing` table so the encounter appears
 * in the Billing Manager ready for review (billed=0, authorized=1).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\Services;

class BillingService
{
    /**
     * Create billing entries for accepted ICD-10 and CPT codes.
     *
     * @param array $params {
     *   pid:         int    — numeric patient ID
     *   encounter:   int    — OpenEMR form_encounter.id
     *   provider_id: int    — provider numeric ID
     *   icd10_codes: array  — [{code, description}]
     *   cpt_codes:   array  — [{code, description}]
     *   user:        int    — logged-in user ID
     *   groupname:   string — practice group name
     * }
     * @return array {success: bool, ids: int[], total: float, error: string|null}
     */
    public function createBillingEntries(array $params): array
    {
        $pid        = (int)($params['pid'] ?? 0);
        $encounter  = (int)($params['encounter'] ?? 0);
        $providerId = (int)($params['provider_id'] ?? 0);
        $icd10Codes = $params['icd10_codes'] ?? [];
        $cptCodes   = $params['cpt_codes'] ?? [];
        $userId     = (int)($params['user'] ?? 0);
        $groupname  = $params['groupname'] ?? 'Default';

        if ($pid === 0) {
            return ['success' => false, 'ids' => [], 'total' => 0.0,
                    'error' => 'Missing pid'];
        }
        if (empty($cptCodes) && empty($icd10Codes)) {
            return ['success' => false, 'ids' => [], 'total' => 0.0,
                    'error' => 'No codes provided'];
        }

        // Idempotency guard — prevent double billing the same encounter
        $row = sqlQuery(
            "SELECT COUNT(*) AS cnt FROM billing WHERE pid = ? AND encounter = ? AND activity = 1",
            [$pid, $encounter]
        );
        $existingCount = $row ? (int)($row['cnt'] ?? 0) : 0;
        if ($existingCount > 0) {
            return ['success' => false, 'ids' => [], 'total' => 0.0,
                    'error' => "Encounter {$encounter} already has billing entries"];
        }

        $createdIds = [];
        $total      = 0.0;

        // Build justify string: ICD-10 codes that justify CPT procedures
        $justifyCodes = array_map(fn($c) => $c['code'] ?? '', $icd10Codes);
        $justifyCodes = array_filter($justifyCodes);
        $justify      = implode(':', $justifyCodes);

        // Insert ICD-10 diagnosis codes (fee=0)
        foreach ($icd10Codes as $icd10) {
            $code = $icd10['code'] ?? '';
            if (empty($code)) {
                continue;
            }
            $id = $this->insertBillingRow([
                'pid'         => $pid,
                'encounter'   => $encounter,
                'provider_id' => $providerId,
                'user'        => $userId,
                'groupname'   => $groupname,
                'code_type'   => 'ICD10',
                'code'        => $code,
                'code_text'   => $icd10['description'] ?? '',
                'units'       => 1,
                'fee'         => 0.00,
                'justify'     => '',
                'authorized'  => 1,
                'billed'      => 0,
                'activity'    => 1,
            ]);
            if ($id) {
                $createdIds[] = $id;
            }
        }

        // Insert CPT procedure codes (fee from schedule)
        foreach ($cptCodes as $cpt) {
            $code = $cpt['code'] ?? '';
            if (empty($code)) {
                continue;
            }
            $fee    = $this->lookupFee($code);
            $total += $fee;
            $id     = $this->insertBillingRow([
                'pid'         => $pid,
                'encounter'   => $encounter,
                'provider_id' => $providerId,
                'user'        => $userId,
                'groupname'   => $groupname,
                'code_type'   => 'CPT4',
                'code'        => $code,
                'code_text'   => $cpt['description'] ?? '',
                'units'       => 1,
                'fee'         => $fee,
                'justify'     => $justify,
                'authorized'  => 1,
                'billed'      => 0,
                'activity'    => 1,
            ]);
            if ($id) {
                $createdIds[] = $id;
            }
        }

        return [
            'success' => true,
            'ids'     => $createdIds,
            'total'   => $total,
            'error'   => null,
        ];
    }

    private function insertBillingRow(array $row): int|false
    {
        $sql = "INSERT INTO billing
                    (date, code_type, code, pid, provider_id, user, groupname,
                     authorized, encounter, code_text, billed, activity,
                     units, fee, justify)
                VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $id = sqlInsert($sql, [
            $row['code_type'], $row['code'], $row['pid'],
            $row['provider_id'], $row['user'], $row['groupname'],
            $row['authorized'], $row['encounter'], $row['code_text'],
            $row['billed'], $row['activity'],
            $row['units'], $row['fee'], $row['justify'],
        ]);

        return $id > 0 ? (int)$id : false;
    }

    /**
     * Return fee for a CPT code. Defaults to $0.00 — clinician sets fee in Billing Manager.
     */
    private function lookupFee(string $cptCode): float
    {
        return 0.00;
    }
}
