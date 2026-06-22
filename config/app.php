<?php

/**
 * Application configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface as TC;

return [
    // Important: this must be a closure rather than a static definition so
    // that a compiled container is guaranteed to align with the runtime.
    'installRoot' => fn () => dirname(__DIR__),

    'legacySiteDirectory' => fn (TC $c) => sprintf(
        '%s/sites/%s',
        $c->getString('installRoot'),
        $c->getString('OPENEMR_SITE'),
    ),
];
