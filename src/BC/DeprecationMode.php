<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC;

enum DeprecationMode
{
    /**
     * Emits a E_USER_DEPRECATED warning, which will be logged.
     */
    case Warning;
    /**
     * Throws an exception. Recommended during development for immediate
     * feedback.
     */
    case Error;
}
