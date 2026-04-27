<?php

/**
 * {@see OidcRefreshAuditLoggerInterface} implementation that writes to the
 * OpenEMR `log` table via the core {@see EventAuditLogger} singleton.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Audit;

use OpenEMR\Common\Logging\EventAuditLogger;

final class DatabaseOidcRefreshAuditLogger implements OidcRefreshAuditLoggerInterface
{
    private const EVENT_TYPE = 'login';
    private const FAIL = 0;
    private const SUCCESS = 1;

    public function discoveryFailed(string $username): void
    {
        $this->failure($username, 'OIDC refresh failed: discovery error');
    }

    public function tokenValidationFailed(string $username): void
    {
        $this->failure($username, 'OIDC refresh failed: token validation');
    }

    public function issuerMismatch(string $username): void
    {
        $this->failure($username, 'OIDC refresh failed: issuer mismatch');
    }

    public function subjectMismatch(string $username): void
    {
        $this->failure($username, 'OIDC refresh failed: subject mismatch');
    }

    public function refreshSucceeded(string $username): void
    {
        EventAuditLogger::getInstance()->newEvent(
            self::EVENT_TYPE,
            $username,
            '',
            self::SUCCESS,
            'OIDC session refreshed',
        );
    }

    private function failure(string $username, string $comment): void
    {
        EventAuditLogger::getInstance()->newEvent(
            self::EVENT_TYPE,
            $username,
            '',
            self::FAIL,
            $comment,
        );
    }
}
