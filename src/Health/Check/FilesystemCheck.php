<?php

/**
 * FilesystemCheck - Verifies filesystem access to documents directory
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health\Check;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Health\HealthCheckInterface;
use OpenEMR\Health\HealthCheckResult;

class FilesystemCheck implements HealthCheckInterface
{
    public function getName(): string
    {
        return 'filesystem';
    }

    public function check(): HealthCheckResult
    {
        try {
            $globals = OEGlobalsBag::getInstance();
            $siteDir = $globals->get('OE_SITE_DIR');

            if (empty($siteDir)) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Site directory not configured'
                );
            }

            $documentsDir = $siteDir . '/documents';

            if (!is_dir($documentsDir)) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Documents directory does not exist'
                );
            }

            if (!is_readable($documentsDir)) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Documents directory is not readable'
                );
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
