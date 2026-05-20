<?php

/**
 * Contract for recording OIDC session-refresh audit events.
 *
 * Symmetric with {@see OidcLoginAuditLoggerInterface} — one method per
 * distinct outcome of the refresh pipeline. Implementations translate
 * these calls into whatever audit-sink format the deployment uses.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Audit;

interface OidcRefreshAuditLoggerInterface
{
    public function discoveryFailed(string $username): void;

    public function tokenValidationFailed(string $username): void;

    public function issuerMismatch(string $username): void;

    public function subjectMismatch(string $username): void;

    public function refreshSucceeded(string $username): void;
}
