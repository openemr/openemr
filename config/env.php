<?php

/**
 * Environment variables
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use function Firehed\Container\env;

return [
    // The unspecified default today is dev=debug, other=warning
    'LOG_LEVEL' => env('LOG_LEVEL', 'warning'),

    // TODO: needing to use the environment directlly is an anti-pattern. This
    // should be made more deliberate. Used by version.php and some ORM setup.
    'OPENEMR__ENVIRONMENT' => env('OPENEMR__ENVIRONMENT', 'dev'),

    // FIXME: this works only for the CLI path, not actual multi-site.
    'OPENEMR_SITE' => env('OPENEMR_SITE', 'default'),
];
