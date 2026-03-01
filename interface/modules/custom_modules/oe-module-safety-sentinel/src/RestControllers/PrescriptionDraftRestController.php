<?php

/**
 * PrescriptionDraftRestController — REST endpoint for creating draft prescriptions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\RestControllers;

use OpenEMR\Modules\SafetySentinel\Services\PrescriptionDraftService;

class PrescriptionDraftRestController
{
    private PrescriptionDraftService $service;

    public function __construct()
    {
        $this->service = new PrescriptionDraftService();
    }

    /**
     * POST /api/safety-sentinel/prescriptions/draft
     * Body: {patient_id, provider_id, encounter, prescriptions: [...]}
     */
    public function createBatch(array $data): array
    {
        $patientId     = (int)($data['patient_id'] ?? 0);
        $providerId    = (int)($data['provider_id'] ?? 0);
        $encounter     = (int)($data['encounter'] ?? 0);
        $prescriptions = $data['prescriptions'] ?? [];

        if ($patientId === 0) {
            http_response_code(400);
            return ['error' => 'Missing patient_id'];
        }

        if (empty($prescriptions)) {
            return ['data' => [['success' => true, 'ids' => [], 'errors' => []]]];
        }

        $result = $this->service->createDraftBatch($patientId, $providerId, $encounter, $prescriptions);

        return [
            'data' => [[
                'success' => $result['success'],
                'ids'     => $result['ids'],
                'errors'  => $result['errors'],
                'count'   => count($result['ids']),
            ]]
        ];
    }
}
