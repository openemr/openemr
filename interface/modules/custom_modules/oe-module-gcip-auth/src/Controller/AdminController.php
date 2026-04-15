<?php

/**
 * Admin controller for GCIP module configuration.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Modules\GcipAuth\Controller;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Modules\GcipAuth\Config\GcipConfigService;

final readonly class AdminController
{
    private GcipConfigService $configService;

    public function __construct()
    {
        $this->configService = new GcipConfigService();
    }

    public function hasAccess(): bool
    {
        return AclMain::aclCheckCore('admin', 'super');
    }

    public function handleAjax(): void
    {
        header('Content-Type: application/json');

        if (!$this->hasAccess()) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $csrfToken = filter_input(INPUT_POST, 'csrf_token') ?? '';
        if (!CsrfUtils::verifyCsrfToken($csrfToken, session: $session)) {
            http_response_code(403);
            echo json_encode(['error' => 'CSRF verification failed']);
            return;
        }

        $action = filter_input(INPUT_POST, 'action') ?? '';

        try {
            match ($action) {
                'save' => $this->saveConfig(),
                'get' => $this->getConfig(),
                default => throw new \InvalidArgumentException('Invalid action'),
            };
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function saveConfig(): void
    {
        $fields = [
            'gcip_firebase_project_id',
            'gcip_firebase_api_key',
            'gcip_firebase_auth_domain',
            'gcip_issuer',
            'gcip_client_id',
            'gcip_allowed_tenant_ids',
        ];

        foreach ($fields as $field) {
            $value = filter_input(INPUT_POST, $field);
            if (is_string($value)) {
                $this->configService->set($field, trim($value));
            }
        }

        echo json_encode(['success' => true]);
    }

    private function getConfig(): void
    {
        echo json_encode(['success' => true, 'data' => $this->configService->getAll()]);
    }
}
