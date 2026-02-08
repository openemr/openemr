<?php

/**
 * MissingSiteException is thrown when the site directory is not properly configured.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\System;

class MissingSiteException extends \RuntimeException
{
    public function __construct(string $message = "Site directory is not configured. OE_SITE_DIR must be set.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
