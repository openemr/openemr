<?php

/**
 * Fixture module config that returns a non-array value, exercising the
 * ZendModuleRouteLoader::load() guard that skips a module whose
 * `module.config.php` does not yield an array.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

return 'not-an-array';
