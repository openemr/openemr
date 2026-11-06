<?php

/**
 * Thrown when a connection to a phiMail server cannot be opened.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final class PhiMailConnectionException extends PhiMailException
{
    /**
     * @param int $socketErrorCode OS error number from the connect attempt (Unreachable only)
     * @param string $socketErrorMessage OS error text from the connect attempt (Unreachable only)
     */
    public function __construct(
        public readonly PhiMailConnectionFailure $failure,
        public readonly int $socketErrorCode = 0,
        public readonly string $socketErrorMessage = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct('phiMail connection failed: ' . $failure->value, 0, $previous);
    }
}
