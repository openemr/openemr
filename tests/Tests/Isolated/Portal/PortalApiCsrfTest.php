<?php

/**
 * The portal/patient entry point checks the CSRF token before it hands the request to the
 * app, and routing takes the request method only from the HTTP request, so a GET can't be
 * routed as a DELETE.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Controllers\Portal\Patient\PortalPageRenderer;
use OpenEMR\Controllers\Portal\Patient\PortalPatientApp;
use OpenEMR\Controllers\Portal\Patient\ProviderController;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PortalRoute;
use OpenEMR\Portal\Routing\PortalRouter;
use OpenEMR\Portal\Routing\RouteAccess;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PortalApiCsrfTest extends TestCase
{
    private const INDEX = __DIR__ . '/../../../../portal/patient/index.php';

    public function testMethodOverridesAreIgnored(): void
    {
        // Symfony applies an override only to a POST, so only a POST can tell the two apart.
        $request = Request::create('/portal/patient/api/thing/5', 'POST');
        $request->headers->set('X-HTTP-Method-Override', 'DELETE');
        $app = new PortalPatientApp(
            new class implements PortalPageRenderer {
                public function render(string $page, array $vars = []): string
                {
                    return 'page:' . $page;
                }
            },
            new PortalRouter([
                new PortalRoute('DELETE', 'api/thing/(:num)', ProviderController::class, 'ErrorApi404', RouteAccess::All, params: ['id' => 2]),
            ]),
        );

        $response = $app->handle($request, 'api/thing/5', PortalPrincipal::staff());

        $this->assertSame('page:DefaultError404', $response->getContent(), 'the POST finds no route; the DELETE route never runs');
    }

    public function testEntryPointChecksTheTokenBeforeDispatching(): void
    {
        $source = file_get_contents(self::INDEX);
        $this->assertIsString($source);
        $ast = (new ParserFactory())->createForHostVersion()->parse($source) ?? [];
        $finder = new NodeFinder();

        $try = $finder->findFirstInstanceOf($ast, TryCatch::class);
        $this->assertInstanceOf(TryCatch::class, $try, 'index.php wraps dispatch in its error handler');

        $guard = $finder->findFirst($try->stmts, static fn(Node $node): bool => $node instanceof StaticCall
            && $node->class instanceof Name && $node->class->getLast() === 'PortalApiCsrfGuard'
            && $node->name instanceof Identifier && $node->name->toString() === 'assertValid');
        $dispatch = $finder->findFirst($try->stmts, static fn(Node $node): bool => $node instanceof MethodCall
            && $node->name instanceof Identifier && $node->name->toString() === 'handle');

        $this->assertInstanceOf(StaticCall::class, $guard, 'index.php checks the CSRF token inside the error handler');
        $this->assertInstanceOf(MethodCall::class, $dispatch, 'index.php hands the request to the app');
        $this->assertLessThan($dispatch->getStartLine(), $guard->getStartLine(), 'the CSRF check runs before dispatch');
        $this->assertStringContainsString(
            '$request->getRealMethod()',
            substr($source, $guard->getStartFilePos(), $guard->getEndFilePos() - $guard->getStartFilePos() + 1),
            'the check uses the real HTTP method, not an override',
        );
    }
}
