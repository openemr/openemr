<?php

/**
 * SessionCheck - Verifies session handler is functional
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health\Check;

use OpenEMR\Health\HealthCheckInterface;
use OpenEMR\Health\HealthCheckResult;

class SessionCheck implements HealthCheckInterface
{
    public const NAME = 'session';

    public function getName(): string
    {
        return static::NAME;
    }

    public function check(): HealthCheckResult
    {
        try {
            $sessionStorageMode = getenv('SESSION_STORAGE_MODE') ?: 'file';

            // Check if session save path is writable for file-based sessions
            if ($sessionStorageMode === 'file') {
                $savePath = session_save_path();
                if (empty($savePath)) {
                    $savePath = sys_get_temp_dir();
                }

                if (!is_writable($savePath)) {
                    return new HealthCheckResult(
                        $this->getName(),
                        false,
                        'Session save path is not writable'
                    );
                }
            }

            // For Redis-based sessions, the cache check will verify connectivity
            // Here we just verify the configuration is valid
            if ($sessionStorageMode === 'predis-sentinel') {
                $sentinels = getenv('REDIS_SENTINELS');
                if (empty($sentinels)) {
                    return new HealthCheckResult(
                        $this->getName(),
                        false,
                        'Redis sentinels not configured'
                    );
                }
            }

            return new HealthCheckResult($this->getName(), true);
        } catch (\Throwable $e) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                $e->getMessage()
            );
        }
    }
}
