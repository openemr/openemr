<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core\Routing\Fixture;

use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Loads the real Application\Controller\IndexController (the migration canary).
 *
 * The zend_modules use the legacy Laminas StandardAutoloader rather than
 * Composer PSR-4, so the controller and its Listener collaborator are required
 * explicitly here. Using the real controller keeps the deprecated JsonModel out
 * of the test source while proving the seam dispatches the genuine canary.
 */
final class CanaryControllerFactory
{
    public static function create(): AbstractActionController
    {
        $base = dirname(__DIR__, 6)
            . '/interface/modules/zend_modules/module/Application/src/Application';
        require_once $base . '/Listener/Listener.php';
        require_once $base . '/Controller/IndexController.php';

        return new \Application\Controller\IndexController();
    }
}
