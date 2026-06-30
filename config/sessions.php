<?php

/**
 * Session-specific configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface as TC;
use OpenEMR\Common\Session\SessionWrapperFactory;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

return [
    SessionWrapperFactory::class => fn (TC $c) => new SessionWrapperFactory(webRoot: $c->getString('webRoot')),
    SessionInterface::class => fn (TC $c) => $c->get(SessionWrapperFactory::class)->getActiveSession(),
];
