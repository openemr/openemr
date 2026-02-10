<?php

/**
 * CDR trigger log report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/clinical_rules.php";

use OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

try {
    $request = Request::createFromGlobals();
    if (empty($request->get('action'))) {
        $request->query->set('action', 'log!view');
    }
    $controllerRouter = new ControllerRouter();
    $response = $controllerRouter->route($request);
} catch (AccessDeniedException | CsrfInvalidException $e) {
    $response = AccessDeniedHelper::createDeniedResponse(
        "ACL check failed for admin/super: Alerts Log - " . $e->getMessage(),
        xl("Alerts Log")
    );
} catch (NotFoundHttpException $e) {
    // Log the exception
    (new SystemLogger())->errorLogCaller($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    $contents = (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))->getTwig()->render('error/404.html.twig');
    // Send the error response
    $response = new Response($contents, 404);
} catch (\Throwable $e) {
    // Log the exception
    (new SystemLogger())->errorLogCaller($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    $contents =  (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))->getTwig()->render('error/general_http_error.html.twig');
    // Send the error response
    $response = new Response($contents, 500);
}

// Send the normal response
$response->send();
exit;
