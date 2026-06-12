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
    private RouteCollection $routes;

    public function __construct(
        private ServiceManager $serviceManager,
        private EventDispatcherInterface $eventDispatcher,
        ZendModuleRouteLoader $routeLoader,
    ) {
        $this->routes = $routeLoader->load();
    }

    /**
     * True if the given path resolves to a known zend module route, i.e. the new
     * runtime is able to serve it. Lets the front controller choose this path
     * over the legacy Laminas runtime without dispatching.
     */
    public function matches(string $pathInfo): bool
    {
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
