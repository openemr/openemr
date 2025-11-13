<?php

/**
 * Front Controller Security Validator.
 *
 * Validates security rules: .inc.php blocking, CLI detection,
 * forbidden paths, and file extension validation.
 *
 * AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    OpenCoreEMR, Inc.
 * @copyright Copyright (c) 2025 OpenCoreEMR, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenCoreEMR\FrontController;

class SecurityValidator
{
    /**
     * Check if running in CLI mode
     */
    public static function isCliMode(): bool
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Check if route is an .inc or .inc.php file
     */
    public static function isIncludeFile(string $route): bool
    {
        return (bool) preg_match('/\.inc(?:\.php)?$/i', $route);
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent(string $message): void
    {
        error_log("OpenEMR Front Controller: " . $message);
    }

    /**
     * Get debug message based on log level
     */
    public static function getDebugMessage(string $defaultMessage, string $debugMessage): string
    {
        return getenv('OPENEMR_FC_LOG_LEVEL') === 'debug' ? $debugMessage : $defaultMessage;
    }
}
