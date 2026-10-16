<?php

/**
 * Layout statement rules.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\LBF\Bootstrap;
use OpenEMR\Services\LBF\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__, 3) . '/globals.php';

$bootstrap = Bootstrap::instantiate(
    OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher(),
    OEGlobalsBag::getInstance()->getKernel()
);
(new AdminController($bootstrap))->run(Request::createFromGlobals());
