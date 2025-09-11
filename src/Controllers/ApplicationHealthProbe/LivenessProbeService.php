<?php

/**
 * Liveness Probe Service for OpenEMR
 *
 * @package   OpenEMR
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEmr Inc.
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\ApplicationHealthProbe;

class LivenessProbeService
{
    /**
     * Perform liveness check
     *
     * This endpoint checks if the application is basically functional
     * by checking if the PHP application processor is working.
     *
     * @return array
     */
    public function check(): array
    {
        return [
            'status' => Status::OK->value,
            'timestamp' => date('c'),
            'http_code' => 200
        ];
    }
}
