<?php

/**
 * Runs a layout-statement page from the existing layout form script.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\LBF;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\LBF\Controller\AdminController;
use OpenEMR\Services\LBF\Controller\GenerateController;
use Symfony\Component\HttpFoundation\Request;

final class StatementEntry
{
    public static function dispatch(string $page): void
    {
        $kernel = OEGlobalsBag::getInstance()->getKernel();
        $bootstrap = Bootstrap::instantiate($kernel->getEventDispatcher(), $kernel);
        $request = Request::createFromGlobals();
        if ($page === 'generate') {
            (new GenerateController($bootstrap))->run($request);
            return;
        }
        if ($page === 'admin') {
            (new AdminController($bootstrap))->run($request);
            return;
        }
        if ($page === 'open') {
            (new OpenForm())->run($request);
            return;
        }
        http_response_code(404);
        echo xlt('That page was not found.');
    }
}
