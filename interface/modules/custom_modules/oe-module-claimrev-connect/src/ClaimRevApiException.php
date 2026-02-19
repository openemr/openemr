<?php

/**
 * Exception thrown when a ClaimRev API call fails.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

class ClaimRevApiException extends ClaimRevException
{
    public function __construct(
        string $message,
        public readonly int $httpStatusCode,
        public readonly string $responseBody,
        public readonly string $endpoint,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatusCode, $previous);
    }
}
