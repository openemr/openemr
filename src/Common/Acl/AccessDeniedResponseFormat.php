<?php

/**
 * Response body format for access denial responses
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Acl;

enum AccessDeniedResponseFormat
{
    case Text;
    case Json;
    /** Suppress default output - use when beforeExit callback handles rendering */
    case None;
}
