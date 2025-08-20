<?php

/**
 * Readiness Probe Service for OpenEMR
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\ApplicationHealthProbe;

use Exception;
use Error;

class ReadinessProbeService
{
    private string $appRoot;

    public function __construct()
    {
        $this->appRoot = realpath(__DIR__ . '/../../..');
    }

    /**
     * Perform readiness check
     *
     * This endpoint checks if the application is ready to serve traffic by
     * verifying database connectivity.
     *
     * @return array
     */
    public function check(): array
    {
        // Set default site directory if not configured
        if (!isset($GLOBALS['OE_SITE_DIR']) || empty($GLOBALS['OE_SITE_DIR'])) {
            $GLOBALS['OE_SITE_DIR'] = $this->appRoot . '/sites/default';
        }

        try {
            // Include the SQL configuration and connection setup
            require_once($this->appRoot . "/library/sql.inc.php");

            // Check if database connection exists
            if (!isset($GLOBALS['adodb']['db']) || !$GLOBALS['adodb']['db']) {
                return $this->createResponse(Status::NOT_READY, 'Database connection not established', 503);
            }

            // Perform a simple database connectivity test
            $test_query = "SELECT 1 as test_connection";
            $result = $GLOBALS['adodb']['db']->ExecuteNoLog($test_query);

            if ($result === false) {
                $error_msg = !empty($GLOBALS['last_mysql_error']) ?
                    $GLOBALS['last_mysql_error'] :
                    $GLOBALS['adodb']['db']->ErrorMsg();

                return $this->createResponse(Status::NOT_READY, 'Database query failed', 503);
            }

            // Verify we can read from a core table (users table should always exist)
            $users_check = "SELECT COUNT(*) as user_count FROM users LIMIT 1";
            $users_result = $GLOBALS['adodb']['db']->ExecuteNoLog($users_check);

            if ($users_result === false) {
                $error_msg = !empty($GLOBALS['last_mysql_error']) ?
                    $GLOBALS['last_mysql_error'] :
                    $GLOBALS['adodb']['db']->ErrorMsg();

                return $this->createResponse(Status::NOT_READY, 'Cannot access core tables', 503);
            }

            // If we get here, all checks passed
            return $this->createResponse(Status::READY, 'Application is ready', 200);
        } catch (Exception $e) {
            return $this->createResponse(Status::NOT_READY, 'Readiness check failed: ' . $e->getMessage(), 503);
        } catch (Error $e) {
            return $this->createResponse(Status::NOT_READY, 'Readiness check failed: ' . $e->getMessage(), 503);
        }
    }

    /**
     * Create standardized response array
     */
    private function createResponse(Status $status, string $message, int $httpCode): array
    {
        return [
            'status' => $status->value,
            'message' => $message,
            'timestamp' => date('c'),
            'http_code' => $httpCode
        ];
    }
}
