<?php

/**
 * Admin Authentication Service
 *
 * Handles authentication for the multi-site administration interface.
 * Authenticates against the default site database using OpenEMR's authentication system.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin;

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;

class AdminAuthService
{
    /**
     * Authenticate admin user credentials
     *
     * @param string $username Username to authenticate
     * @param string $password Password to verify
     * @return array{success: bool, message: string, user_id?: int, username?: string}
     */
    public function authenticate(string $username, string $password): array
    {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Username and password are required'
            ];
        }

        // Ensure we're using the default site
        $_SESSION['site_id'] = 'default';

        // Use OpenEMR's AuthUtils for authentication
        $authUtils = new AuthUtils('login');
        
        // Attempt authentication
        $isValid = $authUtils->confirmPassword($username, $password);
        
        if (!$isValid) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        // Get user details
        $userInfo = $this->getUserInfo($username);
        
        if (!$userInfo) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // Check if user has admin privileges
        if (!$this->isAdminUser($username)) {
            return [
                'success' => false,
                'message' => 'User does not have administrative privileges'
            ];
        }

        return [
            'success' => true,
            'message' => 'Authentication successful',
            'user_id' => $userInfo['id'],
            'username' => $username
        ];
    }

    /**
     * Get user information from database
     *
     * @param string $username Username to look up
     * @return array|null User information or null if not found
     */
    private function getUserInfo(string $username): ?array
    {
        $sql = "SELECT id, username, fname, lname FROM users WHERE username = ? AND active = 1";
        $result = QueryUtils::querySingleRow($sql, [$username]);
        
        return $result ?: null;
    }

    /**
     * Check if user has administrative privileges
     *
     * @param string $username Username to check
     * @return bool True if user is admin
     */
    private function isAdminUser(string $username): bool
    {
        // Check if user belongs to 'Administrators' group
        // Note: gacl_aro.value stores the username, not user ID
        $sql = "SELECT COUNT(*) as count FROM `gacl_groups_aro_map` AS gam
                LEFT JOIN `gacl_aro` AS aro ON gam.aro_id = aro.id
                LEFT JOIN `gacl_aro_groups` AS g ON gam.group_id = g.id
                WHERE aro.value = ? AND g.value = 'admin'";
        
        $result = QueryUtils::querySingleRow($sql, [$username]);
        
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Initialize admin session after successful authentication
     *
     * @param int $userId User ID
     * @param string $username Username
     * @return void
     */
    public function initializeSession(int $userId, string $username): void
    {
        $_SESSION['authUser'] = $username;
        $_SESSION['authUserID'] = $userId;
        $_SESSION['site_id'] = 'default';
        $_SESSION['admin_login'] = true;
        $_SESSION['admin_login_time'] = time();
    }

    /**
     * Check if current session is authenticated as admin
     *
     * @return bool True if authenticated
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['admin_login']) 
            && $_SESSION['admin_login'] === true
            && isset($_SESSION['authUserID'])
            && isset($_SESSION['site_id']);
    }

    /**
     * Destroy admin session
     *
     * @return void
     */
    public function logout(): void
    {
        unset($_SESSION['authUser']);
        unset($_SESSION['authUserID']);
        unset($_SESSION['admin_login']);
        unset($_SESSION['admin_login_time']);
        session_destroy();
    }

    /**
     * Check session timeout (30 minutes default)
     *
     * @param int $timeoutMinutes Timeout in minutes
     * @return bool True if session is valid
     */
    public function checkSessionTimeout(int $timeoutMinutes = 30): bool
    {
        if (!isset($_SESSION['admin_login_time'])) {
            return false;
        }

        $elapsed = time() - $_SESSION['admin_login_time'];
        
        if ($elapsed > ($timeoutMinutes * 60)) {
            $this->logout();
            return false;
        }

        // Refresh session time
        $_SESSION['admin_login_time'] = time();
        return true;
    }
}
