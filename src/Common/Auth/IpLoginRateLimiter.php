<?php

/**
 * IP-based login rate limiting service.
 *
 * Extracted from AuthUtils to provide reusable IP-based brute-force protection
 * for all authentication paths (password, Google Sign-In, OIDC).
 *
 * Uses the existing `ip_tracking` table and the same global settings:
 * - ip_max_failed_logins
 * - ip_time_reset_password_max_failed_logins
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth;

use MyMailer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

final class IpLoginRateLimiter
{
    private const TABLE = 'ip_tracking';

    /**
     * Ensure the given IP address has a tracking record.
     *
     * Must be called before any check/increment operations for that IP.
     */
    public function ensureTracked(string $ipString): void
    {
        if ($ipString === '') {
            $ipString = 'blank';
        }

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_string` FROM `' . self::TABLE . '` WHERE `ip_string` = ?',
            [$ipString],
        );

        if ($rows === []) {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `' . self::TABLE . '` (`ip_string`) VALUES (?)',
                [$ipString],
            );
        }
    }

    /**
     * Check whether the given IP address is currently blocked.
     */
    public function checkBlocked(string $ipString): IpBlockStatus
    {
        if ($ipString === '') {
            $ipString = 'blank';
        }

        $globals = OEGlobalsBag::getInstance();
        $maxFailed = $globals->getInt('ip_max_failed_logins');

        if ($maxFailed === 0) {
            return IpBlockStatus::allowed();
        }

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_auto_block_emailed`, `ip_force_block`, `ip_no_prevent_timing_attack`,'
            . ' `ip_login_fail_counter`,'
            . ' TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) AS `seconds_since_last_fail`'
            . ' FROM `' . self::TABLE . '` WHERE `ip_string` = ?',
            [$ipString],
        );

        if ($rows === []) {
            return IpBlockStatus::allowed();
        }

        $row = $rows[0];

        // Manually force-blocked
        if (((int) ($row['ip_force_block'] ?? 0)) === 1) {
            $skipTiming = ((int) ($row['ip_no_prevent_timing_attack'] ?? 0)) === 1;
            return IpBlockStatus::blocked(
                forceBlocked: true,
                skipTimingAttack: $skipTiming,
                requiresEmailNotification: false,
            );
        }

        $failCounter = (int) ($row['ip_login_fail_counter'] ?? 0);

        if ($failCounter < $maxFailed) {
            return IpBlockStatus::allowed();
        }

        // Counter exceeds threshold — check timeout-based reset
        $timeoutSeconds = $globals->getInt('ip_time_reset_password_max_failed_logins');
        if ($timeoutSeconds > 0) {
            $secondsSinceLastFail = (int) ($row['seconds_since_last_fail'] ?? 0);
            if ($secondsSinceLastFail > $timeoutSeconds) {
                $this->resetCounter($ipString);
                return IpBlockStatus::allowed();
            }
        }

        $needsEmail = ((int) ($row['ip_auto_block_emailed'] ?? 0)) === 0;
        return IpBlockStatus::blocked(
            forceBlocked: false,
            skipTimingAttack: false,
            requiresEmailNotification: $needsEmail,
        );
    }

    /**
     * Record a failed login attempt from the given IP address.
     *
     * Increments the fail counter. If a timeout-based reset is configured and
     * the last failure was longer ago than the timeout, the counter resets to 1
     * instead of accumulating.
     */
    public function recordFailedAttempt(string $ipString): void
    {
        if ($ipString === '') {
            $ipString = 'blank';
        }

        $globals = OEGlobalsBag::getInstance();
        $timeoutSeconds = $globals->getInt('ip_time_reset_password_max_failed_logins');

        if ($timeoutSeconds > 0) {
            $rows = QueryUtils::fetchRecords(
                'SELECT TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) AS `seconds_since_last_fail`'
                . ' FROM `' . self::TABLE . '` WHERE `ip_string` = ?',
                [$ipString],
            );

            if ($rows !== []) {
                $secondsSinceLastFail = (int) ($rows[0]['seconds_since_last_fail'] ?? 0);
                if ($secondsSinceLastFail > $timeoutSeconds) {
                    QueryUtils::sqlStatementThrowException(
                        'UPDATE `' . self::TABLE . '` SET'
                        . ' `total_ip_login_fail_counter` = `total_ip_login_fail_counter` + 1,'
                        . ' `ip_login_fail_counter` = 1,'
                        . ' `ip_last_login_fail` = NOW(),'
                        . ' `ip_auto_block_emailed` = 0'
                        . ' WHERE `ip_string` = ?',
                        [$ipString],
                    );
                    return;
                }
            }
        }

        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET'
            . ' `total_ip_login_fail_counter` = `total_ip_login_fail_counter` + 1,'
            . ' `ip_login_fail_counter` = `ip_login_fail_counter` + 1,'
            . ' `ip_last_login_fail` = NOW()'
            . ' WHERE `ip_string` = ?',
            [$ipString],
        );
    }

    /**
     * Reset the fail counter for the given IP address after a successful login.
     */
    public function recordSuccessfulLogin(string $ipString): void
    {
        if ($ipString === '') {
            $ipString = 'blank';
        }

        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET'
            . ' `ip_login_fail_counter` = 0,'
            . ' `ip_last_login_fail` = NULL,'
            . ' `ip_auto_block_emailed` = 0'
            . ' WHERE `ip_string` = ?',
            [$ipString],
        );
    }

    /**
     * Send an admin email notification that an IP address has been auto-blocked.
     */
    public function notifyBlock(string $ipString): bool
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET `ip_auto_block_emailed` = 1 WHERE `ip_string` = ?',
            [$ipString],
        );

        $globals = OEGlobalsBag::getInstance();
        $senderEmail = $globals->getString('patient_reminder_sender_email');
        $returnPath = $globals->getString('practice_return_email_path');

        if ($senderEmail === '' || $returnPath === '') {
            error_log(
                'Unable to send OpenEMR admin email notification since either'
                . ' patient_reminder_sender_email or practice_return_email_path global was not set',
            );
            return false;
        }

        $timeoutSeconds = $globals->getInt('ip_time_reset_password_max_failed_logins');
        $message = $timeoutSeconds > 0
            ? "IP address '" . text($ipString) . "' has been temporarily blocked."
            : "IP address '" . text($ipString) . "' has been blocked.";

        return MyMailer::emailServiceQueue(
            $senderEmail,
            $returnPath,
            xl('IP Address Block Notification For OpenEMR Admin'),
            $message,
        );
    }

    // -----------------------------------------------------------------------
    // Admin operations (used by ip_tracker.php and login_counter_ip_tracker.php)
    // -----------------------------------------------------------------------

    /**
     * Query IP tracking records for the admin report.
     *
     * @return list<array<string, mixed>>
     */
    public static function collectFailedLogins(
        bool $showOnlyWithCount,
        bool $showOnlyManuallyBlocked,
        bool $showOnlyAutoBlocked,
    ): array {
        $where = [];
        $binds = [];

        if ($showOnlyWithCount) {
            $where[] = '`ip_login_fail_counter` > 0';
        }

        if ($showOnlyManuallyBlocked) {
            $where[] = '`ip_force_block` = 1';
        }

        if ($showOnlyAutoBlocked) {
            $globals = OEGlobalsBag::getInstance();
            $maxFailed = $globals->getInt('ip_max_failed_logins');
            if ($maxFailed !== 0) {
                $timeoutSeconds = $globals->getInt('ip_time_reset_password_max_failed_logins');
                if ($timeoutSeconds > 0) {
                    $where[] = '`ip_login_fail_counter` > ? AND TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) < ?';
                    $binds[] = $maxFailed;
                    $binds[] = $timeoutSeconds;
                } else {
                    $where[] = '`ip_login_fail_counter` > ?';
                    $binds[] = $maxFailed;
                }
            }
        }

        $whereClause = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        return QueryUtils::fetchRecords(
            'SELECT `id`, `ip_string`, `ip_force_block`, `ip_no_prevent_timing_attack`,'
            . ' `total_ip_login_fail_counter`, `ip_login_fail_counter`, `ip_last_login_fail`,'
            . ' TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) AS `seconds_last_ip_login_fail`'
            . ' FROM `' . self::TABLE . '` ' . $whereClause
            . ' ORDER BY `ip_last_login_fail` DESC, `total_ip_login_fail_counter` DESC',
            $binds,
        );
    }

    /**
     * Reset the fail counter for an IP by its tracking record ID.
     */
    public static function resetCounterById(int $ipId): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET'
            . ' `ip_login_fail_counter` = 0,'
            . ' `ip_last_login_fail` = NULL,'
            . ' `ip_auto_block_emailed` = 0'
            . ' WHERE `id` = ?',
            [$ipId],
        );
    }

    /**
     * Manually block an IP address by its tracking record ID.
     */
    public static function forceBlock(int $ipId): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET `ip_force_block` = 1 WHERE `id` = ?',
            [$ipId],
        );
    }

    /**
     * Remove a manual block from an IP address by its tracking record ID.
     */
    public static function unblock(int $ipId): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET `ip_force_block` = 0 WHERE `id` = ?',
            [$ipId],
        );
    }

    /**
     * Disable timing attack prevention for an IP by its tracking record ID.
     */
    public static function disableTimingAttackPrevention(int $ipId): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET `ip_no_prevent_timing_attack` = 1 WHERE `id` = ?',
            [$ipId],
        );
    }

    /**
     * Enable timing attack prevention for an IP by its tracking record ID.
     */
    public static function enableTimingAttackPrevention(int $ipId): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET `ip_no_prevent_timing_attack` = 0 WHERE `id` = ?',
            [$ipId],
        );
    }

    private function resetCounter(string $ipString): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE . '` SET'
            . ' `ip_login_fail_counter` = 0,'
            . ' `ip_last_login_fail` = NULL,'
            . ' `ip_auto_block_emailed` = 0'
            . ' WHERE `ip_string` = ?',
            [$ipString],
        );
    }
}
