<?php

/**
 * OEGlobalsBag-backed audit settings implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

use OpenEMR\Core\OEGlobalsBag;

/**
 * Reads audit settings from OEGlobalsBag.
 */
final class GlobalsAuditSettings implements AuditSettingsInterface
{
    public function __construct(
        private readonly OEGlobalsBag $globals,
    ) {
    }

    public function isAuditingEnabled(): bool
    {
        return $this->globals->getBoolean('enable_auditlog');
    }

    public function isQueryLoggingEnabled(): bool
    {
        return $this->globals->getBoolean('audit_events_query');
    }

    public function isEventTypeEnabled(AuditEventType $eventType): bool
    {
        $key = 'audit_events_' . $eventType->value;
        return $this->globals->getBoolean($key);
    }

    public function isBreakglassLoggingForced(): bool
    {
        return $this->globals->getBoolean('gbl_force_log_breakglass');
    }

    public function isEncryptionEnabled(): bool
    {
        return $this->globals->getBoolean('enable_auditlog_encryption');
    }
}
