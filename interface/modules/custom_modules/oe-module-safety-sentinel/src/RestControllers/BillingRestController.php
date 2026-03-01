<?php

/**
 * BillingRestController — REST endpoint for creating billing entries.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\RestControllers;

use OpenEMR\Modules\SafetySentinel\Services\BillingService;

class BillingRestController
{
    private BillingService $service;

    public function __construct()
    {
        $this->service = new BillingService();
    }

    /**
     * POST /api/safety-sentinel/billing
     * Body: {pid, encounter, provider_id, icd10_codes, cpt_codes, user, groupname}
     */
    public function create(array $data): array
    {
        $result = $this->service->createBillingEntries($data);

        if (!$result['success']) {
            http_response_code(400);
            return ['error' => $result['error']];
        }

        return [
            'data' => [[
                'ids'   => $result['ids'],
                'total' => $result['total'],
                'count' => count($result['ids']),
            ]]
        ];
    }
}
