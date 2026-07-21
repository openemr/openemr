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

namespace OpenEMR\Tests\Isolated\Core\Routing;

use Laminas\Mvc\Controller\AbstractActionController;
use OpenEMR\Core\Routing\ZendControllerLocatorInterface;
use OpenEMR\Core\Routing\ZendModelResponder;
use OpenEMR\Core\Routing\ZendModuleControllerResolver;
use OpenEMR\Core\Routing\ZendModuleRouteLoader;
use OpenEMR\Tests\Isolated\Core\Routing\Fixture\CanaryControllerFactory;
use OpenEMR\Tests\Isolated\Core\Routing\Fixture\FixtureActionController;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Group('isolated')]
#[Group('core')]
class ZendModuleControllerResolverIsolatedTest extends TestCase
{
    private function locator(AbstractActionController $controller, string $name = 'Fixture'): ZendControllerLocatorInterface
    {
        return new class ($controller, $name) implements ZendControllerLocatorInterface {
            public function __construct(
                private readonly AbstractActionController $controller,
                private readonly string $name,
            ) {
            }

            public function has(string $controllerName): bool
            {
                return $controllerName === $this->name;
            }

            public function get(string $controllerName): AbstractActionController
            {
                if ($controllerName !== $this->name) {
                    throw new \RuntimeException('unknown');
                }
                return $this->controller;
            }
        };
    }

    private function responder(): ZendModelResponder
    {
        return new ZendModelResponder(
            // The ViewModel branch is covered in the responder test.
            fn($model): string => '<rendered>',
        );
    }

    private function requestFor(string $controller, ?string $action): Request
    {
        $request = Request::create('/fixture');
        $request->attributes->set(ZendModuleRouteLoader::ATTR_CONTROLLER, $controller);
        if ($action !== null) {
            $request->attributes->set(ZendModuleRouteLoader::ATTR_ACTION, $action);
        }
        return $request;
    }

    public function testCanaryRealIndexControllerReturnsEmptyJson(): void
    {
        // End-to-end through the seam with the genuine canary controller:
        // Application\Controller\IndexController::indexAction returns
        // JsonModel([]) -> the responder maps it to a JsonResponse "[]",
        // identical to the Laminas runtime output.
        $resolver = new ZendModuleControllerResolver(
            $this->locator(CanaryControllerFactory::create()),
            $this->responder(),
        );

        $controller = $resolver->getController($this->requestFor('Fixture', 'index'));
        $this->assertIsCallable($controller);

        $response = $controller();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame('[]', $response->getContent());
    }

    public function testActionNameConvertedToMethodAndResponsePassThrough(): void
    {
        // "response" -> responseAction, which returns a Symfony Response that
        // the responder passes through unchanged.
        $resolver = new ZendModuleControllerResolver(
            $this->locator(new FixtureActionController()),
            $this->responder(),
        );

        $controller = $resolver->getController($this->requestFor('Fixture', 'response'));
        $this->assertIsCallable($controller);
        $response = $controller();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('from-controller', $response->getContent());
    }

    public function testStringResultBecomesHtmlResponse(): void
    {
        $resolver = new ZendModuleControllerResolver(
            $this->locator(new FixtureActionController()),
            $this->responder(),
        );

        $controller = $resolver->getController($this->requestFor('Fixture', 'html'));
        $this->assertIsCallable($controller);
        $response = $controller();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('<p>html-action</p>', $response->getContent());
    }

    public function testMissingActionDefaultsToIndex(): void
    {
        // No action attribute -> resolver defaults to "index" -> indexAction.
        // AbstractActionController provides a default indexAction returning a
        // ViewModel, so the dispatch resolves and the responder renders it
        // through the injected view renderer (proving the default was applied).
        $resolver = new ZendModuleControllerResolver(
            $this->locator(new FixtureActionController()),
            $this->responder(),
        );

        $controller = $resolver->getController($this->requestFor('Fixture', null));
        $this->assertIsCallable($controller);

        $response = $controller();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('<rendered>', $response->getContent());
    }

    public function testNoControllerAttributeReturnsFalse(): void
    {
        $resolver = new ZendModuleControllerResolver(
            $this->locator(new FixtureActionController()),
            $this->responder(),
        );

        $request = Request::create('/fixture');
        $this->assertFalse($resolver->getController($request));
    }

    public function testUnknownControllerThrowsNotFound(): void
    {
        $resolver = new ZendModuleControllerResolver(
            $this->locator(new FixtureActionController(), 'Fixture'),
            $this->responder(),
        );

        $controller = $resolver->getController($this->requestFor('Nonexistent', 'response'));
        $this->assertIsCallable($controller);

        $this->expectException(NotFoundHttpException::class);
        $controller();
    }

    public function testUnknownActionMethodThrowsNotFound(): void
    {
        $resolver = new ZendModuleControllerResolver(
            $this->locator(new FixtureActionController()),
            $this->responder(),
        );

        $controller = $resolver->getController($this->requestFor('Fixture', 'does-not-exist'));
        $this->assertIsCallable($controller);

        $this->expectException(NotFoundHttpException::class);
        $controller();
    }

    public function testGetArgumentsIsEmpty(): void
    {
        $resolver = new ZendModuleControllerResolver(
            $this->locator(new FixtureActionController()),
            $this->responder(),
        );

        $this->assertSame([], $resolver->getArguments(Request::create('/'), fn() => null));
    }
}
