<?php

/**
 * Status Enum for OpenEMR Probes
 *
 * @package   OpenEMR
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEmr Inc.
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\ApplicationHealthProbe;

enum Status: string
{
    case OK = 'ok';               // PHP is basically functional
    case READY = 'ready';         // Application can connect to db and is ready to serve traffic
    case NOT_READY = 'not_ready'; // Application cannot serve traffic
}
