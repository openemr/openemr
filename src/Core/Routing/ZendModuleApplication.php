<?php

/**
 * The replacement runtime for the legacy Laminas MVC zend_modules.
 *
 * This is the modern half of the strangler-pattern seam: it routes a request
 * with Symfony routing, dispatches the existing zend controller through
 * OEHttpKernel via ZendModuleControllerResolver, and returns a Symfony
 * Response. It runs alongside the legacy Laminas runtime — a request is served
 * by one or the other, never both — so modules can migrate off laminas-mvc
 * (which has no PHP 8.5 support) incrementally.
 *
 * The legacy ServiceManager is reused so controllers are built by their
 * existing module factories, and the Laminas view renderer is reused so
 * ViewModel results render identically during migration.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Routing;

use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use OpenEMR\Core\OEHttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class ZendModuleApplication
{
    /**
     * Routes the new runtime is allowed to serve. The resolver shim dispatches
     * controllers by calling the action method directly, which does NOT set up
     * the Laminas MVC request/route-match/MvcEvent context that most legacy
     * controllers depend on (e.g. AclController's $this->params() /
     * $this->getRequest()). Until that context bridge exists, only controllers
     * that don't touch it are safe — so the seam serves an explicit allowlist of
     * proven canary routes and lets everything else fall through to the legacy
     * Laminas runtime, even when the feature flag is on.
     *
     * The canary (Application IndexController::indexAction -> JsonModel([])) uses
     * none of that context. Routes graduate onto this list as each controller is
     * migrated (Plan 3).
     *
     * @var list<string>
     */
    private const CANARY_PATHS = [
        '/application',
    ];

    private RouteCollection $routes;

    public function __construct(
        private ServiceManager $serviceManager,
        private EventDispatcherInterface $eventDispatcher,
        ZendModuleRouteLoader $routeLoader,
    ) {
        $this->routes = $routeLoader->load();
    }

    /**
     * True if the new runtime should serve this path: it must resolve to a known
     * zend module route AND be on the canary allowlist. Non-canary routes return
     * false so the front controller leaves them on the legacy Laminas runtime.
     */
    public function matches(string $pathInfo): bool
    {
        if (!in_array($pathInfo, self::CANARY_PATHS, true)) {
            return false;
        }

        $matcher = new UrlMatcher($this->routes, new RequestContext());
        try {
            $matcher->match($pathInfo);
            return true;
        } catch (\Symfony\Component\Routing\Exception\ExceptionInterface) {
            return false;
        }
    }

    /**
     * Match, dispatch, and return the response for the request. The caller is
     * responsible for sending it.
     */
    public function handle(Request $request): Response
    {
        $matcher = new UrlMatcher($this->routes, (new RequestContext())->fromRequest($request));
        $parameters = $matcher->match($request->getPathInfo());

        foreach ($parameters as $key => $value) {
            $request->attributes->set($key, $value);
        }

        $resolver = $this->buildResolver();

        $kernel = $this->buildKernel($resolver);

        return $kernel->handle($request, HttpKernel::MAIN_REQUEST, false);
    }

    private function buildResolver(): ZendModuleControllerResolver
    {
        $controllerManager = $this->serviceManager->get('ControllerManager');
        if (!$controllerManager instanceof ControllerManager) {
            throw new \RuntimeException('Module ServiceManager did not provide a ControllerManager');
        }

        $locator = new ServiceManagerControllerLocator($controllerManager);
        $responder = new ZendModelResponder($this->viewRenderer());

        return new ZendModuleControllerResolver($locator, $responder);
    }

    private function buildKernel(ControllerResolverInterface&ArgumentResolverInterface $resolver): OEHttpKernel
    {
        return new OEHttpKernel(
            $this->eventDispatcher,
            $resolver,
            null,
            $resolver,
        );
    }

    /**
     * @return \Closure(ViewModel): string
     */
    private function viewRenderer(): \Closure
    {
        $renderer = $this->serviceManager->get('ViewRenderer');
        if (!$renderer instanceof RendererInterface) {
            throw new \RuntimeException('ViewRenderer service is not a Laminas renderer');
        }

        return static fn(ViewModel $model): string => $renderer->render($model);
    }
}
