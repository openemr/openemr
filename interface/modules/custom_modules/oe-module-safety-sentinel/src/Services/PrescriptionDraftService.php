<?php

/**
 * PrescriptionDraftService — creates draft prescription records in OpenEMR.
 *
 * Prescriptions are created with request_intent='proposal' (draft) so the
 * clinician must explicitly sign/activate them in the prescriptions module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\Services;

class PrescriptionDraftService
{
    // Maps free-text form names to OpenEMR drug_form list_options
    private const FORM_MAP = [
        'tablet'    => 'tablet',
        'capsule'   => 'capsule',
        'liquid'    => 'solution',
        'solution'  => 'solution',
        'cream'     => 'cream',
        'ointment'  => 'ointment',
        'inhaler'   => 'aerosol',
        'aerosol'   => 'aerosol',
        'patch'     => 'patch',
        'injection' => 'injection',
        'drops'     => 'drops',
    ];

    // Maps free-text frequency to OpenEMR drug_interval list_options
    private const INTERVAL_MAP = [
        'once daily'        => 'once_daily',
        'daily'             => 'once_daily',
        'once a day'        => 'once_daily',
        'qd'                => 'once_daily',
        'twice daily'       => 'twice_daily',
        'bid'               => 'twice_daily',
        'twice a day'       => 'twice_daily',
        'three times daily' => 'three_times_daily',
        'tid'               => 'three_times_daily',
        'three times a day' => 'three_times_daily',
        'four times daily'  => 'four_times_daily',
        'qid'               => 'four_times_daily',
        'four times a day'  => 'four_times_daily',
        'every 8 hours'     => 'every_8_hours',
        'q8h'               => 'every_8_hours',
        'every 6 hours'     => 'every_6_hours',
        'q6h'               => 'every_6_hours',
        'every 12 hours'    => 'twice_daily',
        'q12h'              => 'twice_daily',
        'as needed'         => 'as_needed',
        'prn'               => 'as_needed',
    ];

    /**
     * Create a draft prescription in OpenEMR's prescriptions table.
     *
     * @param array $params {
     *   patient_id:   int    — numeric pid
     *   provider_id:  int    — prescribing provider
     *   encounter:    int    — OpenEMR encounter ID (optional)
     *   medication:   string
     *   dosage:       string — "500mg"
     *   form:         string — "capsule"
     *   route:        string — "oral"
     *   frequency:    string — "three times daily"
     *   duration:     string — "7 days"
     *   quantity:     string — "21"
     *   refills:      int
     *   instructions: string — patient-facing sig
     *   diagnosis:    string — indication (optional)
     * }
     * @return array {success: bool, id: int|null, error: string|null}
     */
    public function createDraft(array $params): array
    {
        $patientId  = (int)($params['patient_id'] ?? 0);
        $providerId = (int)($params['provider_id'] ?? 0);
        $encounter  = (int)($params['encounter'] ?? 0);

        if ($patientId === 0) {
            return ['success' => false, 'id' => null, 'error' => 'Missing patient_id'];
        }

        $uuid = \OpenEMR\Common\Uuid\UuidRegistry::getRegistryForTable('prescriptions')->createUuid();

        // form and interval are int FK columns — leave NULL so clinician sets them on signing
        $sql = "INSERT INTO prescriptions
                    (uuid, patient_id, provider_id, encounter, date_added, date_modified,
                     start_date, drug, dosage, quantity, route, refills,
                     note, active, request_intent, request_intent_title,
                     drug_dosage_instructions, diagnosis)
                VALUES (?, ?, ?, ?, NOW(), NOW(),
                        CURDATE(), ?, ?, ?, ?,
                        ?, ?, 1, 'proposal', 'Proposal',
                        ?, ?)";

        $id = sqlInsert($sql, [
            $uuid,
            $patientId,
            $providerId,
            $encounter ?: null,
            $params['medication'] ?? '',
            $params['dosage'] ?? '',
            $params['quantity'] ?? '1',
            $params['route'] ?? 'oral',
            (int)($params['refills'] ?? 0),
            $params['instructions'] ?? '',
            $params['instructions'] ?? '',
            $params['diagnosis'] ?? '',
        ]);
        if ($id === 0) {
            return ['success' => false, 'id' => null, 'error' => 'Insert failed'];
        }

        return ['success' => true, 'id' => $id, 'error' => null];
    }

    /**
     * Create multiple draft prescriptions from a batch request.
     *
     * @param int   $patientId
     * @param int   $providerId
     * @param int   $encounter
     * @param array $prescriptions Array of prescription param arrays.
     * @return array {success: bool, ids: int[], errors: string[]}
     */
    public function createDraftBatch(int $patientId, int $providerId, int $encounter, array $prescriptions): array
    {
        $ids    = [];
        $errors = [];

        foreach ($prescriptions as $rx) {
            $rx['patient_id']  = $patientId;
            $rx['provider_id'] = $providerId;
            $rx['encounter']   = $encounter;
            $result = $this->createDraft($rx);
            if ($result['success']) {
                $ids[] = $result['id'];
            } else {
                $drug = $rx['medication'] ?? 'unknown';
                $errors[] = "{$drug}: {$result['error']}";
            }
        }

        return [
            'success' => count($errors) === 0,
            'ids'     => $ids,
            'errors'  => $errors,
        ];
    }
}
