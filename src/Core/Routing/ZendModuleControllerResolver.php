<?php

/**
 * Dispatches legacy Laminas MVC zend_module controllers from the modern
 * OEHttpKernel during the strangler-pattern migration off laminas-mvc.
 *
 * Symfony's HttpKernel asks a ControllerResolverInterface for a callable and an
 * ArgumentResolverInterface for its arguments. This shim returns a callable that:
 *   1. reads the controller FQCN + action from the matched route attributes
 *      (set by ZendModuleRouteLoader),
 *   2. locates the existing AbstractActionController via the module
 *      ServiceManager (factories reused unchanged),
 *   3. invokes the action method using the Laminas action->method convention,
 *   4. converts the JsonModel/ViewModel/Response/string result into a Symfony
 *      Response via ZendModelResponder.
 *
 * The legacy controllers are dispatched unchanged; only the surrounding runtime
 * is replaced.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Routing;

use Laminas\Mvc\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class ZendModuleControllerResolver implements ControllerResolverInterface, ArgumentResolverInterface
{
    public function __construct(
        private ZendControllerLocatorInterface $locator,
        private ZendModelResponder $responder,
    ) {
    }

    public function getController(Request $request): callable|false
    {
        $controllerName = $request->attributes->get(ZendModuleRouteLoader::ATTR_CONTROLLER);
        if (!is_string($controllerName) || $controllerName === '') {
            return false;
        }

        $action = $request->attributes->get(ZendModuleRouteLoader::ATTR_ACTION, 'index');
        $action = is_string($action) && $action !== '' ? $action : 'index';
        $method = AbstractController::getMethodFromAction($action);

        return function () use ($controllerName, $method): Response {
            if (!$this->locator->has($controllerName)) {
                throw new NotFoundHttpException();
            }

            $controller = $this->locator->get($controllerName);
            if (!method_exists($controller, $method)) {
                throw new NotFoundHttpException();
            }

            $result = $controller->$method();

            return $this->responder->toResponse($result);
        };
    }

    /**
     * The controller closure captures everything it needs, so no arguments are
     * resolved from the request here.
     *
     * @return array<int, mixed>
     */
    public function getArguments(Request $request, callable $controller, ?\ReflectionFunctionAbstract $reflector = null): array
    {
        return [];
    }
}
