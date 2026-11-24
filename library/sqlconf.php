<?php

/**
 * Deprecated: no longer loads database settings. Kept so third-party code
 * that requires it does not fail outright.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2022-2023 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\BC\Deprecation;

Deprecation::emit('library/sqlconf.php no longer loads database settings and will be removed. Use OpenEMR\BC\DatabaseConnectionOptions::forSite() instead.');
