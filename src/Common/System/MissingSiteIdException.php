<?php

/**
 * MissingSiteIdException is thrown when the session does not contain a site ID.
 *
 * This is distinct from MissingSiteException (which covers the site directory
 * not being configured).  Both share a common parent so callers can catch
 * either the specific scenario or the broader "site cannot be identified" family.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\System;

class MissingSiteIdException extends MissingSiteException
{
    public function __construct(
        string $message = 'Site ID is missing from session data.',
        int $code = 400,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
