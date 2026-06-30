<?php

/**
 * Auditing-related services and configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface as TC;
use OpenEMR\Common\Database\{
    ConnectionManager,
    ConnectionType,
};
use OpenEMR\Common\Logging\{
    BreakglassChecker,
    BreakglassCheckerInterface,
};

return [
    BreakglassCheckerInterface::class => BreakglassChecker::class,
    // See notes in BreakglassChecker's constructor: it must use the
    // non-audited connection in order to avoid an infinite loop w/ SQL logging
    BreakglassChecker::class => fn (TC $c) => new BreakglassChecker(
        $c->get(ConnectionManager::class)->get(ConnectionType::NonAudited),
    ),
];
