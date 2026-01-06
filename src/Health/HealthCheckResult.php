<?php

/**
 * HealthCheckResult - Value object representing the result of a health check
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health;

final readonly class HealthCheckResult
{
    public function __construct(
        public string $name,
        public bool $healthy,
        public ?string $message = null
    ) {
    }

    public function toArray(): array
    {
        $result = [
            'healthy' => $this->healthy,
        ];

        if ($this->message !== null) {
            $result['message'] = $this->message;
        }

        return $result;
    }
}
