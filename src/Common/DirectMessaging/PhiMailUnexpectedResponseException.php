<?php

/**
 * Thrown when a phiMail server answers a command with something other than OK.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final class PhiMailUnexpectedResponseException extends PhiMailException
{
    /**
     * @param string $response the server's reply line, including its line feed
     */
    public function __construct(public readonly string $response, ?\Throwable $previous = null)
    {
        parent::__construct('Unexpected phiMail server response', 0, $previous);
    }
}
