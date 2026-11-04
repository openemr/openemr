<?php

/**
 * Every OnsiteDocument API method checks access to the document's patient before it reads or
 * changes anything: portal patients may reach only their own documents, and staff need the
 * portal ACL plus the patients/docs ACL. The controller needs the Phreeze runtime, so this reads
 * its source to check each method makes the right PortalPatientAccessGuard call.
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
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OnsiteDocumentControllerAccessTest extends TestCase
{
    private const CONTROLLER = __DIR__ . '/../../../../portal/patient/libs/Controller/OnsiteDocumentController.php';

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function methodChecksProvider(): array
    {
        return [
            'documents page' => ['ListView', 'assertCanReadDocuments'],
            'list' => ['Query', 'assertCanReadDocuments'],
            'read' => ['Read', 'assertCanReadDocuments'],
            'create' => ['Create', 'assertCanAddDocument'],
            'update' => ['Update', 'assertCanWriteDocuments'],
            'delete' => ['Delete', 'assertCanWriteDocuments'],
        ];
    }

    #[DataProvider('methodChecksProvider')]
    public function testMethodChecksAccessToTheDocumentsPatient(string $method, string $check): void
    {
        $this->assertContains($check, $this->guardCallsIn($this->method($method)));
    }

    /**
     * @return array<string, array{string, list<array{string, string}>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function guardArgumentsProvider(): array
    {
        return [
            'documents page' => ['ListView', [['assertCanReadDocuments', '$pid']]],
            'list' => ['Query', [['assertCanReadDocuments', '$pid']]],
            'read' => ['Read', [['assertCanReadDocuments', '$onsitedocument->Pid']]],
            'create' => ['Create', [['assertCanAddDocument', '$onsitedocument->Pid']]],
            'update' => ['Update', [
                // before loading: the owner on file
                ['assertCanWriteDocuments', 'self::documentOwner($pk)'],
                // after loading, in case the document moved in between
                ['assertCanWriteDocuments', '$owner'],
                // the patient the document is saved under
                ['assertCanWriteDocuments', '$onsitedocument->Pid'],
            ]],
            'delete' => ['Delete', [['assertCanWriteDocuments', '$onsitedocument->Pid']]],
        ];
    }

    /**
     * @param list<array{string, string}> $expected guard method and patient expression, in order
     */
    #[DataProvider('guardArgumentsProvider')]
    public function testGuardChecksTheDocumentsPatient(string $method, array $expected): void
    {
        $printer = new Standard();
        $actual = [];
        foreach ($this->guardStaticCalls($this->method($method)) as $call) {
            $this->assertCount(1, $call->args, "{$method}() passes one patient to the guard");
            $argument = $call->args[0];
            $this->assertInstanceOf(Arg::class, $argument);
            $this->assertInstanceOf(Identifier::class, $call->name);
            $actual[] = [$call->name->toString(), $printer->prettyPrintExpr($argument->value)];
        }

        $this->assertSame($expected, $actual);
    }

    public function testUpdateTakesTheOwnerFromTheLoadedDocument(): void
    {
        $owners = (new NodeFinder())->find(
            $this->method('Update')->stmts ?? [],
            static fn(Node $node): bool => $node instanceof Assign
                && $node->var instanceof Variable
                && $node->var->name === 'owner',
        );

        $this->assertCount(1, $owners);
        $this->assertInstanceOf(Assign::class, $owners[0]);
        $this->assertSame('$onsitedocument->Pid', (new Standard())->prettyPrintExpr($owners[0]->expr));
    }

    public function testQueryPinsThePatientAfterTheRequestFilters(): void
    {
        $finder = new NodeFinder();
        $statements = $this->method('Query')->stmts ?? [];
        $applyLine = null;
        $pinLines = [];
        foreach ($finder->find($statements, static fn(Node $node): bool => $node instanceof MethodCall || $node instanceof Assign) as $node) {
            if (
                $node instanceof MethodCall
                && $node->name instanceof Identifier
                && $node->name->toString() === 'ApplyRequestEqualsFilters'
            ) {
                $applyLine = $node->getStartLine();
            }
            if (
                $node instanceof Assign
                && $node->var instanceof PropertyFetch
                && $node->var->name instanceof Identifier
                && $node->var->name->toString() === 'Pid_Equals'
                && $node->expr instanceof Variable
                && $node->expr->name === 'pid'
            ) {
                $pinLines[] = $node->getStartLine();
            }
        }

        $this->assertIsInt($applyLine, 'Query applies the request filters');
        $this->assertNotEmpty(
            array_filter($pinLines, static fn(int $line): bool => $line > $applyLine),
            'a request key such as pid=… must not replace the checked patient',
        );
    }

    private function method(string $name): ClassMethod
    {
        $source = file_get_contents(self::CONTROLLER);
        $this->assertIsString($source);
        $ast = (new ParserFactory())->createForHostVersion()->parse($source) ?? [];
        $method = (new NodeFinder())->findFirst(
            $ast,
            static fn(Node $node): bool => $node instanceof ClassMethod && $node->name->toString() === $name,
        );
        $this->assertInstanceOf(ClassMethod::class, $method, "OnsiteDocumentController::{$name}() exists");

        return $method;
    }

    /**
     * @return list<string> the PortalPatientAccessGuard methods called, in source order
     */
    private function guardCallsIn(ClassMethod $method): array
    {
        $calls = [];
        foreach ($this->guardStaticCalls($method) as $call) {
            if ($call->name instanceof Identifier) {
                $calls[] = $call->name->toString();
            }
        }

        return $calls;
    }

    /**
     * @return list<StaticCall> the PortalPatientAccessGuard calls, in source order
     */
    private function guardStaticCalls(ClassMethod $method): array
    {
        $calls = [];
        foreach ((new NodeFinder())->findInstanceOf($method->stmts ?? [], StaticCall::class) as $call) {
            if ($call->class instanceof Name && $call->class->getLast() === 'PortalPatientAccessGuard') {
                $calls[] = $call;
            }
        }

        return $calls;
    }
}
