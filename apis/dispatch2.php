<?php

/**
 * Rest Dispatch
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// below brings in autoloader
require_once "../vendor/autoload.php";

use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use OpenEMR\RestControllers\Subscriber\SiteSetupListener;
use OpenEMR\RestControllers\Subscriber\AuthorizationListener;
use OpenEMR\RestControllers\Subscriber\ExceptionHandlerListener;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\Subscriber\RoutesExtensionListener;
use OpenEMR\RestControllers\Subscriber\SessionCleanupListener;
use Symfony\Component\HttpFoundation\RequestStack;

// create the Request object
$request = HttpRestRequest::createFromGlobals();
$eventDispatcher = new EventDispatcher();
// need to handle request finish and session cleanup listeners first before any other listeners
$eventDispatcher->addSubscriber(new ExceptionHandlerListener());
// this listener will handle the telemetry data collection at the end of the request
$eventDispatcher->addSubscriber(new TelemetryListener());
// this listener will handle the session cleanup at the end of the request unless its a local api request
$eventDispatcher->addSubscriber(new SessionCleanupListener());

// site setup will handle the site id, db connection, and globals setup
$eventDispatcher->addSubscriber(new SiteSetupListener());
// TODO: @adunsulag if we can use the security component here eventually, or rename this to be AuthenticationListener
$eventDispatcher->addSubscriber(new AuthorizationListener());
$eventDispatcher->addSubscriber(new RoutesExtensionListener());

// handle conversion of controller objects to request responses (json, text, etc).
$eventDispatcher->addSubscriber(new ViewRendererListener());

// create your controller and argument resolvers
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();
$kernel = new OEHttpKernel($eventDispatcher, $controllerResolver, new RequestStack(), $argumentResolver);
// actually execute the kernel, which turns the request into a response
// by dispatching events, calling a controller, and returning the response
// events dispatched are:
//   kernel.request -> RequestEvent
//   kernel.controller -> ControllerEvent
//   kernel.controller_arguments -> ControllerArgumentsEvent
//   kernel.view -> ViewEvent
//   kernel.response -> ResponseEvent
//   kernel.finish_request -> FinishRequestEvent
//   kernel.exception -> ExceptionEvent
try {

    $response = $kernel->handle($request);

    // send the headers and echo the content
    $response->send();

    // trigger the kernel.terminate event
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    // TODO: handle exceptions properly
    error_log($e->getMessage());
    // should never get here, but if we do, we can return a generic error response
    die("An error occurred while processing the request. Please check the logs for more details.");
}
