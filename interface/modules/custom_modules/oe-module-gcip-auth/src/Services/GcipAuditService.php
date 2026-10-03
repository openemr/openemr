<?php

/**
 * GCIP Audit Service
 * 
 * <!-- AI-Generated Content Start -->
 * This service handles comprehensive audit logging for GCIP authentication
 * events, providing security monitoring and compliance tracking for all
 * authentication activities within the module.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR\Modules\GcipAuth\Services
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\GcipAuth\Services;

use OpenEMR\Common\Logging\EventAuditLogger;

/**
 * Service for audit logging of GCIP authentication events
 */
class GcipAuditService
{
    /**
     * Event types for GCIP audit logging - AI-Generated
     */
    private const EVENT_TYPES = [
        'AUTH_LOGIN' => 'Authentication Login',
        'AUTH_LOGOUT' => 'Authentication Logout', 
        'AUTH_FAILED' => 'Authentication Failed',
        'TOKEN_REFRESH' => 'Token Refresh',
        'TOKEN_EXPIRED' => 'Token Expired',
        'CONFIG_CHANGE' => 'Configuration Change',
        'USER_CREATED' => 'User Auto-Created',
        'DOMAIN_BLOCKED' => 'Domain Access Blocked'
    ];

    /**
     * Log authentication attempt
     * 
     * <!-- AI-Generated Content Start -->
     * Records an authentication attempt event with details about the user,
     * outcome, and any relevant context for security monitoring.
     * <!-- AI-Generated Content End -->
     *
     * @param string $username Username attempting authentication
     * @param string $eventType Type of authentication event
     * @param string $description Event description
     * @param array $additionalData Additional context data
     */
    public function logAuthenticationAttempt(
        string $username, 
        string $eventType, 
        string $description, 
        array $additionalData = []
    ): void {
        $this->logEvent(
            self::EVENT_TYPES['AUTH_LOGIN'] ?? 'Authentication Event',
            $description,
            array_merge([
                'username' => $username,
                'event_type' => $eventType,
                'source' => 'GCIP Authentication Module',
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ], $additionalData)
        );
    }

    /**
     * Log successful authentication
     * 
     * <!-- AI-Generated Content Start -->
     * Records a successful GCIP authentication event with user details
     * and authentication context for security and usage tracking.
     * <!-- AI-Generated Content End -->
     *
     * @param string $username Authenticated username
     * @param array $userInfo User information from GCIP token
     */
    public function logSuccessfulAuthentication(string $username, array $userInfo = []): void
    {
        $this->logEvent(
            self::EVENT_TYPES['AUTH_LOGIN'],
            "Successful GCIP authentication for user: {$username}",
            [
                'username' => $username,
                'email' => $userInfo['email'] ?? null,
                'name' => $userInfo['name'] ?? null,
                'success' => true,
                'authentication_method' => 'GCIP/Google OAuth2',
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]
        );
    }

    /**
     * Log failed authentication
     * 
     * <!-- AI-Generated Content Start -->
     * Records a failed GCIP authentication attempt with error details
     * for security monitoring and troubleshooting purposes.
     * <!-- AI-Generated Content End -->
     *
     * @param string $username Attempted username or email
     * @param string $reason Failure reason
     * @param array $context Additional failure context
     */
    public function logFailedAuthentication(string $username, string $reason, array $context = []): void
    {
        $this->logEvent(
            self::EVENT_TYPES['AUTH_FAILED'],
            "Failed GCIP authentication for user: {$username}. Reason: {$reason}",
            array_merge([
                'username' => $username,
                'failure_reason' => $reason,
                'success' => false,
                'authentication_method' => 'GCIP/Google OAuth2',
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ], $context)
        );
    }

    /**
     * Log logout event
     * 
     * <!-- AI-Generated Content Start -->
     * Records a user logout event with session cleanup details for
     * comprehensive session tracking and security auditing.
     * <!-- AI-Generated Content End -->
     *
     * @param string $username Username logging out
     */
    public function logLogout(string $username): void
    {
        $this->logEvent(
            self::EVENT_TYPES['AUTH_LOGOUT'],
            "GCIP authenticated user logged out: {$username}",
            [
                'username' => $username,
                'action' => 'logout',
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'session_cleanup' => true
            ]
        );
    }

    /**
     * Log token refresh event
     * 
     * <!-- AI-Generated Content Start -->
     * Records token refresh operations for monitoring token lifecycle
     * and detecting potential security issues with token management.
     * <!-- AI-Generated Content End -->
     *
     * @param string $username Username whose token was refreshed
     * @param bool $success Whether refresh was successful
     */
    public function logTokenRefresh(string $username, bool $success): void
    {
        $this->logEvent(
            self::EVENT_TYPES['TOKEN_REFRESH'],
            "GCIP token refresh " . ($success ? 'successful' : 'failed') . " for user: {$username}",
            [
                'username' => $username,
                'action' => 'token_refresh',
                'success' => $success,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * Log configuration change
     * 
     * <!-- AI-Generated Content Start -->
     * Records changes to GCIP module configuration for administrative
     * auditing and change tracking purposes.
     * <!-- AI-Generated Content End -->
     *
     * @param string $adminUser Administrator making the change
     * @param string $setting Configuration setting changed
     * @param string $action Change action (created, updated, deleted)
     */
    public function logConfigurationChange(string $adminUser, string $setting, string $action): void
    {
        $this->logEvent(
            self::EVENT_TYPES['CONFIG_CHANGE'],
            "GCIP configuration change by {$adminUser}: {$action} setting '{$setting}'",
            [
                'admin_user' => $adminUser,
                'setting' => $setting,
                'action' => $action,
                'module' => 'GCIP Authentication',
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]
        );
    }

    /**
     * Log user auto-creation event
     * 
     * <!-- AI-Generated Content Start -->
     * Records automatic user account creation during GCIP authentication
     * for tracking new user provisioning and security monitoring.
     * <!-- AI-Generated Content End -->
     *
     * @param string $username Created username
     * @param string $email User email from GCIP
     * @param array $userDetails Additional user details
     */
    public function logUserAutoCreation(string $username, string $email, array $userDetails = []): void
    {
        $this->logEvent(
            self::EVENT_TYPES['USER_CREATED'],
            "Auto-created user account via GCIP authentication: {$username} ({$email})",
            array_merge([
                'username' => $username,
                'email' => $email,
                'action' => 'user_auto_creation',
                'source' => 'GCIP Authentication',
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ], $userDetails)
        );
    }

    /**
     * Log domain restriction violation
     * 
     * <!-- AI-Generated Content Start -->
     * Records attempts to authenticate with email domains that are not
     * in the allowed domains list for security monitoring.
     * <!-- AI-Generated Content End -->
     *
     * @param string $email Email address blocked
     * @param string $domain Blocked domain
     */
    public function logDomainBlocked(string $email, string $domain): void
    {
        $this->logEvent(
            self::EVENT_TYPES['DOMAIN_BLOCKED'],
            "GCIP authentication blocked due to domain restriction: {$email} (domain: {$domain})",
            [
                'email' => $email,
                'blocked_domain' => $domain,
                'action' => 'domain_block',
                'security_event' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]
        );
    }

    /**
     * Log security event
     * 
     * <!-- AI-Generated Content Start -->
     * Records general security-related events for the GCIP authentication
     * module including suspicious activities and security violations.
     * <!-- AI-Generated Content End -->
     *
     * @param string $eventType Type of security event
     * @param string $description Event description
     * @param array $context Event context data
     */
    public function logSecurityEvent(string $eventType, string $description, array $context = []): void
    {
        $this->logEvent(
            "Security Event: {$eventType}",
            $description,
            array_merge([
                'event_type' => $eventType,
                'security_event' => true,
                'module' => 'GCIP Authentication',
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ], $context)
        );
    }

    /**
     * Write event to audit log
     * 
     * <!-- AI-Generated Content Start -->
     * Core method for writing audit events to OpenEMR's audit logging
     * system with proper formatting and data serialization.
     * <!-- AI-Generated Content End -->
     *
     * @param string $eventType Event type/category
     * @param string $description Event description
     * @param array $data Event data
     */
    private function logEvent(string $eventType, string $description, array $data = []): void
    {
        try {
            // Format data for logging - AI-Generated
            $logData = [
                'module' => 'GCIP Authentication',
                'event_type' => $eventType,
                'description' => $description,
                'data' => $data,
                'logged_at' => date('Y-m-d H:i:s')
            ];

            // Use OpenEMR's event audit logger - AI-Generated
            EventAuditLogger::instance()->newEvent(
                $eventType,
                $_SESSION['authUser'] ?? $data['username'] ?? 'system',
                $_SESSION['authProvider'] ?? 'GCIP',
                1, // success flag
                $description,
                '', // patient_id (not applicable for auth events)
                json_encode($logData)
            );

        } catch (\Exception $e) {
            // Fallback to error log if audit logging fails - AI-Generated
            error_log("GCIP Audit Logging Error: " . $e->getMessage() . " | Event: " . $description);
        }
    }

    /**
     * Get audit event statistics
     * 
     * <!-- AI-Generated Content Start -->
     * Retrieves statistics about GCIP authentication events for reporting
     * and monitoring dashboard purposes.
     * <!-- AI-Generated Content End -->
     *
     * @param int $days Number of days to look back
     * @return array Statistics array
     */
    public function getEventStatistics(int $days = 30): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        try {
            // Query audit events for statistics - AI-Generated
            $sql = "SELECT 
                        event,
                        COUNT(*) as count,
                        DATE(date) as event_date
                    FROM log_comment_encrypt 
                    WHERE date >= ? 
                        AND (comments LIKE '%GCIP%' OR event LIKE '%Authentication%')
                    GROUP BY event, DATE(date)
                    ORDER BY event_date DESC";
            
            $result = sqlStatementCdrEngine($sql, [$startDate]);
            
            $statistics = [];
            while ($row = sqlFetchArray($result)) {
                $statistics[] = $row;
            }
            
            return $statistics;
            
        } catch (\Exception $e) {
            error_log("Failed to retrieve GCIP audit statistics: " . $e->getMessage());
            return [];
        }
    }
}