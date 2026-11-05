<?php

/**
 * The portal's Phreeze entry point checks the CSRF token before it dispatches, and the router
 * takes the request method only from the HTTP request, so a GET can't be routed as a DELETE.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use RequestUtil;

final class PortalApiCsrfTest extends TestCase
{
    private const INDEX = __DIR__ . '/../../../../portal/patient/index.php';

    /** @var array<mixed> */
    private array $savedServer;
    /** @var array<mixed> */
    private array $savedRequest;

    protected function setUp(): void
    {
        $this->savedServer = $_SERVER;
        $this->savedRequest = $_REQUEST;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->savedServer;
        $_REQUEST = $this->savedRequest;
    }

    public function testMethodOverridesAreIgnored(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'DELETE';
        $_REQUEST['_method'] = 'DELETE';

        $this->assertSame('GET', RequestUtil::GetMethod());
    }

    public function testTheRealMethodIsUsed(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        unset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'], $_REQUEST['_method']);

        $this->assertSame('PUT', RequestUtil::GetMethod());
    }

    public function testEntryPointChecksTheTokenBeforeDispatching(): void
    {
        $source = file_get_contents(self::INDEX);
        $this->assertIsString($source);
        $ast = (new ParserFactory())->createForHostVersion()->parse($source) ?? [];
        $finder = new NodeFinder();

        $try = $finder->findFirstInstanceOf($ast, TryCatch::class);
        $this->assertInstanceOf(TryCatch::class, $try, 'index.php wraps dispatch in its error handler');

        $guardLine = null;
        $dispatchLine = null;
        foreach ($finder->findInstanceOf($try->stmts, StaticCall::class) as $call) {
            if (!$call->class instanceof Name || !$call->name instanceof Identifier) {
                continue;
            }
            $target = $call->class->getLast() . '::' . $call->name->toString();
            if ($target === 'PortalApiCsrfGuard::assertValid') {
                $guardLine ??= $call->getStartLine();
            }
            if ($target === 'Dispatcher::Dispatch') {
                $dispatchLine ??= $call->getStartLine();
            }
        }

        $this->assertIsInt($guardLine, 'index.php checks the CSRF token inside the error handler');
        $this->assertIsInt($dispatchLine, 'index.php dispatches the request');
        $this->assertLessThan($dispatchLine, $guardLine, 'the CSRF check runs before dispatch');
        $this->assertStringContainsString(
            '$request->getRealMethod()',
            $this->callSource($source, $finder, $try->stmts),
            'the check uses the real HTTP method, not an override',
        );
    }

    /**
     * @param array<Node> $statements
     */
    private function callSource(string $source, NodeFinder $finder, array $statements): string
    {
        $call = $finder->findFirst(
            $statements,
            static fn(Node $node): bool => $node instanceof StaticCall
                && $node->name instanceof Identifier
                && $node->name->toString() === 'assertValid',
        );
        $this->assertInstanceOf(StaticCall::class, $call);

        return substr($source, $call->getStartFilePos(), $call->getEndFilePos() - $call->getStartFilePos() + 1);
    }
}
