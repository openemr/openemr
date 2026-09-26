<?php

/**
 * Rules editor entry.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\LbfStatements\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__, 4) . "/globals.php";

$bootstrap = Bootstrap::instantiate(
    OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher(),
    OEGlobalsBag::getInstance()->getKernel()
);
(new AdminController($bootstrap))->run(Request::createFromGlobals());
