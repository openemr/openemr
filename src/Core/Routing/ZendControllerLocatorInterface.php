<?php

/**
 * Locates legacy Laminas MVC controllers for the strangler-pattern dispatch
 * seam.
 *
 * The production implementation pulls controllers from the zend_modules
 * ServiceManager's ControllerManager, so each module's existing controller
 * factory (DI wiring, dependencies) is reused unchanged. Tests can substitute
 * a trivial in-memory locator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Routing;

use Laminas\Mvc\Controller\AbstractActionController;

interface ZendControllerLocatorInterface
{
    public function has(string $controllerName): bool;

    /**
     * @throws \RuntimeException if the controller cannot be located or built
     */
    public function get(string $controllerName): AbstractActionController;
}
