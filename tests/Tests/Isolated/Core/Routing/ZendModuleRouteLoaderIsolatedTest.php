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

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use OpenEMR\Core\Routing\ZendModuleRouteLoader;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

#[Group('isolated')]
#[Group('core')]
class ZendModuleRouteLoaderIsolatedTest extends TestCase
{
    private function loader(): ZendModuleRouteLoader
    {
        // Point at the real zend_modules tree so the loader is exercised
        // against production route data, not a fixture.
        return new ZendModuleRouteLoader(
            dirname(__DIR__, 5) . '/interface/modules/zend_modules',
        );
    }

    /**
     * The modules these tests assert routes for. In production this list comes
     * from ModulesApplication's ApplicationConfig (core_modules + DB-enabled
     * plugins); the loader only requires configs for modules on it.
     *
     * @return list<string>
     */
    private function enabledModules(): array
    {
        return ['Acl', 'Application', 'Documents', 'Carecoordination'];
    }

    public function testLoadsRealModuleRoutes(): void
    {
        $collection = $this->loader()->load($this->enabledModules());

        // A representative sample of modules that declare routes.
        $this->assertNotNull($collection->get('zend_module.Acl.acl'));
        $this->assertNotNull($collection->get('zend_module.Application.home'));
        $this->assertNotNull($collection->get('zend_module.Application.application'));
        $this->assertNotNull($collection->get('zend_module.Documents.documents'));
    }

    public function testSegmentRouteTranslatesPlaceholdersAndConstraints(): void
    {
        $route = $this->loader()->load($this->enabledModules())->get('zend_module.Acl.acl');
        self::assertNotNull($route);

        $this->assertSame('/acl/{action}/{id}', $route->getPath());
        // action carries the Laminas route default; id has no default, so it
        // becomes a null default to stay optional.
        $this->assertSame('index', $route->getDefault('action'));
        $this->assertNull($route->getDefault('id'));
        $this->assertSame('Acl', $route->getDefault(ZendModuleRouteLoader::ATTR_MODULE));
        $this->assertSame('[0-9]+', $route->getRequirement('id'));
        $this->assertSame('[a-zA-Z][a-zA-Z0-9_-]*', $route->getRequirement('action'));
    }

    public function testAclRouteMatchesWithAndWithoutOptionalSegments(): void
    {
        $matcher = new UrlMatcher($this->loader()->load($this->enabledModules()), new RequestContext());

        $bare = $matcher->match('/acl');
        $this->assertSame('Acl', $bare[ZendModuleRouteLoader::ATTR_MODULE]);
        // Action default comes from the Laminas config.
        $this->assertSame('index', $bare['action']);

        $withAction = $matcher->match('/acl/list');
        $this->assertSame('list', $withAction['action']);

        $withId = $matcher->match('/acl/edit/42');
        $this->assertSame('edit', $withId['action']);
        $this->assertSame('42', $withId['id']);
    }

    public function testCanaryApplicationIndexRoute(): void
    {
        $matcher = new UrlMatcher($this->loader()->load($this->enabledModules()), new RequestContext());

        // The canary: GET /application -> Application\Controller\IndexController::index
        $match = $matcher->match('/application');
        $this->assertSame('Application', $match[ZendModuleRouteLoader::ATTR_MODULE]);
        $this->assertSame('index', $match['action']);
        $controller = $match['controller'];
        $this->assertIsString($controller);
        $this->assertStringContainsString('IndexController', $controller);
    }

    public function testChildRoutesAreLoadedUnderParentPattern(): void
    {
        $collection = $this->loader()->load($this->enabledModules());

        $sendto = $collection->get('zend_module.Application.application/sendto');
        self::assertNotNull($sendto);
        $this->assertSame('/application/sendto/{action}', $sendto->getPath());
    }

    public function testDuplicatePlaceholdersAreDeduplicated(): void
    {
        // Carecoordination's encounterccdadispatch has a malformed Laminas
        // pattern with repeated :id and :val. The translation must produce a
        // valid Symfony path (no duplicate placeholder names).
        $route = $this->loader()->load($this->enabledModules())->get('zend_module.Carecoordination.encounterccdadispatch');
        self::assertNotNull($route);

        $this->assertSame('/encounterccdadispatch/{action}/{id}/{val}', $route->getPath());
    }

    public function testLiteralRootRoute(): void
    {
        $route = $this->loader()->load($this->enabledModules())->get('zend_module.Application.home');
        self::assertNotNull($route);
        $this->assertSame('/', $route->getPath());
    }

    public function testDisabledModulesAreNotLoaded(): void
    {
        // Acl ships routes and a config on disk, but is absent from the enabled
        // list, so the loader must not require its config or emit its routes.
        $collection = $this->loader()->load(['Application']);

        $this->assertNotNull($collection->get('zend_module.Application.application'));
        $this->assertNull($collection->get('zend_module.Acl.acl'));
        $this->assertNull($collection->get('zend_module.Documents.documents'));
    }

    public function testLoadModuleConfigWithLiteralAndSegmentTypes(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        $config = [
            'router' => [
                'routes' => [
                    'lit' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/widget',
                            'defaults' => ['controller' => 'WidgetController', 'action' => 'show'],
                        ],
                    ],
                    'seg' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/widget[/:action][/:id]',
                            'constraints' => ['id' => '[0-9]+'],
                            'defaults' => ['controller' => 'WidgetController', 'action' => 'index'],
                        ],
                    ],
                ],
            ],
        ];

        $loader->loadModuleConfig('Widget', $config, $collection);

        $lit = $collection->get('zend_module.Widget.lit');
        $seg = $collection->get('zend_module.Widget.seg');
        self::assertNotNull($lit);
        self::assertNotNull($seg);
        $this->assertSame('/widget', $lit->getPath());
        $this->assertSame('/widget/{action}/{id}', $seg->getPath());
        $this->assertSame('[0-9]+', $seg->getRequirement('id'));
    }

    public function testModuleWithoutRoutesProducesNothing(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        $loader->loadModuleConfig('NoRoutes', ['view_manager' => []], $collection);

        $this->assertCount(0, $collection);
    }

    public function testNonArrayRoutesProducesNothing(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        $loader->loadModuleConfig('Bad', ['router' => ['routes' => 'not-an-array']], $collection);

        $this->assertCount(0, $collection);
    }

    public function testNonArrayRouteNodeIsSkipped(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        $config = [
            'router' => [
                'routes' => [
                    'scalar' => 'not-a-node',
                    'good' => [
                        'options' => [
                            'route' => '/good',
                            'defaults' => ['controller' => 'GoodController', 'action' => 'index'],
                        ],
                    ],
                ],
            ],
        ];

        $loader->loadModuleConfig('Mixed', $config, $collection);

        $this->assertNull($collection->get('zend_module.Mixed.scalar'));
        $this->assertNotNull($collection->get('zend_module.Mixed.good'));
    }

    public function testNonArrayChildRouteIsSkipped(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        $config = [
            'router' => [
                'routes' => [
                    'parent' => [
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/parent',
                            'defaults' => ['controller' => 'ParentController', 'action' => 'index'],
                        ],
                        'child_routes' => [
                            'scalarChild' => 'not-a-node',
                        ],
                    ],
                ],
            ],
        ];

        $loader->loadModuleConfig('Parented', $config, $collection);

        $this->assertNotNull($collection->get('zend_module.Parented.parent'));
        $this->assertNull($collection->get('zend_module.Parented.parent/scalarChild'));
    }

    public function testRouteWithUntranslatablePatternIsSkipped(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        // A pattern that does not start with "/" has no usable Symfony path, so
        // translatePattern() returns null and buildRoute() emits no route.
        $config = [
            'router' => [
                'routes' => [
                    'noslash' => [
                        'options' => [
                            'route' => 'novalidpath',
                            'defaults' => ['controller' => 'X', 'action' => 'index'],
                        ],
                    ],
                ],
            ],
        ];

        $loader->loadModuleConfig('Slashless', $config, $collection);

        $this->assertCount(0, $collection);
    }

    public function testBareColonIsTreatedAsLiteral(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        // A ":" not followed by a placeholder name is emitted literally rather
        // than starting a placeholder.
        $config = [
            'router' => [
                'routes' => [
                    'colon' => [
                        'options' => [
                            'route' => '/ratio/:/value',
                            'defaults' => ['controller' => 'X', 'action' => 'index'],
                        ],
                    ],
                ],
            ],
        ];

        $loader->loadModuleConfig('Colon', $config, $collection);

        $route = $collection->get('zend_module.Colon.colon');
        self::assertNotNull($route);
        $this->assertSame('/ratio/:/value', $route->getPath());
    }

    public function testRouteWithoutDefaultsStillGetsModuleDefault(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        // No 'defaults' key -> normalizeDefaults(null) -> []. The module name
        // default is still attached.
        $config = [
            'router' => [
                'routes' => [
                    'plain' => [
                        'options' => ['route' => '/plain'],
                    ],
                ],
            ],
        ];

        $loader->loadModuleConfig('Plain', $config, $collection);

        $route = $collection->get('zend_module.Plain.plain');
        self::assertNotNull($route);
        $this->assertSame('Plain', $route->getDefault(ZendModuleRouteLoader::ATTR_MODULE));
    }

    public function testNonStringAndNonScalarDefaultsAreFiltered(): void
    {
        $loader = new ZendModuleRouteLoader('/nonexistent');
        $collection = new RouteCollection();

        $config = [
            'router' => [
                'routes' => [
                    'filtered' => [
                        'options' => [
                            'route' => '/filtered',
                            'defaults' => [
                                0 => 'dropped-int-key',
                                'nested' => ['dropped-array-value'],
                                'controller' => 'KeptController',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $loader->loadModuleConfig('Filtered', $config, $collection);

        $route = $collection->get('zend_module.Filtered.filtered');
        self::assertNotNull($route);
        $defaults = $route->getDefaults();
        $this->assertSame('KeptController', $defaults['controller']);
        $this->assertArrayNotHasKey(0, $defaults);
        $this->assertArrayNotHasKey('nested', $defaults);
    }

    public function testNonArrayModuleConfigFileIsSkipped(): void
    {
        // load() reads each module's module.config.php; the NonArrayConfig
        // fixture returns a string, so the loader skips it without error.
        $loader = new ZendModuleRouteLoader(__DIR__ . '/Fixture/modules');
        $collection = $loader->load(['NonArrayConfig']);

        $this->assertCount(0, $collection);
    }

    public function testMissingModuleRootProducesEmptyCollection(): void
    {
        // load() against a directory with no module/ subdir hits the is_dir
        // guard in moduleConfigFiles() and yields nothing rather than letting
        // Finder throw.
        $loader = new ZendModuleRouteLoader('/nonexistent');

        $this->assertCount(0, $loader->load(['Application']));
    }
}
