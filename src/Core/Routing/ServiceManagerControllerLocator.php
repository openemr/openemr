<?php

/**
 * Production controller locator backed by the zend_modules ServiceManager.
 *
 * Resolves controllers through the Laminas ControllerManager so the existing
 * per-module controller factories run exactly as they do under laminas-mvc —
 * the strangler seam reuses the legacy DI wiring rather than re-implementing it.
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
use Laminas\Mvc\Controller\ControllerManager;

final readonly class ServiceManagerControllerLocator implements ZendControllerLocatorInterface
{
    public function __construct(
        private ControllerManager $controllerManager,
    ) {
    }

    public function has(string $controllerName): bool
    {
        return $this->controllerManager->has($controllerName);
    }

    public function get(string $controllerName): AbstractActionController
    {
        if (!$this->controllerManager->has($controllerName)) {
            throw new \RuntimeException('Unknown zend module controller');
        }

        $controller = $this->controllerManager->get($controllerName);
        if (!$controller instanceof AbstractActionController) {
            throw new \RuntimeException('Resolved controller is not an AbstractActionController');
        }

        return $controller;
    }
}
