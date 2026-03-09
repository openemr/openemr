<?php

/**
 * Interface for audit settings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric.stern@gmail.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

/**
 * Provides access to audit logging configuration settings.
 *
 * This interface abstracts the global settings to enable testing
 * without depending on OEGlobalsBag.
 */
interface AuditSettingsInterface
{
    /**
     * Check if audit logging is enabled globally.
     */
    public function isAuditingEnabled(): bool;

    /**
     * Check if SELECT query logging is enabled.
     */
    public function isQueryLoggingEnabled(): bool;

    /**
     * Check if logging is enabled for a specific event type.
     */
    public function isEventTypeEnabled(AuditEventType $eventType): bool;

    /**
     * Check if breakglass user logging is forced.
     *
     * When true, breakglass users have all queries logged regardless
     * of other audit settings.
     */
    public function isBreakglassLoggingForced(): bool;

    /**
     * Check if audit log encryption is enabled.
     */
    public function isEncryptionEnabled(): bool;
}
