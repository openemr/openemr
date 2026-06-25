<?php

declare(strict_types=1);

namespace OpenEMR\Api;

use OpenEMR\Plugins\PluginManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\RoutingResults;

class Router
{
    /** @var App<ContainerInterface> */
    private App $app;

    public function __construct(
        ContainerInterface $container,
        private PluginManager $pluginManager,
    ) {
        // Set up Slim with the container for route handler resolution
        $this->app = AppFactory::create(
            container: $container,
        );
    }

    /**
     * Routes and, if routable, executes the request and returns its response.
     *
     * This will return null on 404/405 routing results, to allow for legacy
     * routing through FallbackRouter. In the future where the fallback is no
     * longer needed, this will return a response on 404 and 405 as well.
     */
    public function route(ServerRequestInterface $request): ?ResponseInterface
    {
        $this->pluginManager->addRoutes($this->app);

        $resolver = $this->app->getRouteResolver();
        $routingResults = $resolver->computeRoutingResults(
            $request->getUri()->getPath(),
            $request->getMethod(),
        );

        if ($routingResults->getRouteStatus() !== RoutingResults::FOUND) {
            // For now, do nothing instead of 404/405'ing. This allows the BC
            // routing fallback path to deal with it.
            return null;
        }

        return $this->app->handle($request);
    }
}
