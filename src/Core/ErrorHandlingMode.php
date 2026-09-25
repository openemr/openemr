<?php

/**
 * Controls how ErrorHandler reacts when a PHP error passes the
 * error_reporting() filter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core;

enum ErrorHandlingMode
{
    /**
     * PHP Errors are logged but do not change control flow
     */
    case Log;

    /**
     * PHP Errors are converted to ErrorExceptions and thrown
     */
    case Throw;
}
