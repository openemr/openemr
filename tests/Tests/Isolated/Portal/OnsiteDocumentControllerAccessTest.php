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

    public function testUpdateChecksTheTargetPatientWhenADocumentMoves(): void
    {
        $calls = array_filter(
            $this->guardCallsIn($this->method('Update')),
            static fn(string $call): bool => $call === 'assertCanWriteDocuments',
        );

        $this->assertCount(2, $calls, 'Update checks the current patient and the patient it moves the document to');
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
        foreach ((new NodeFinder())->findInstanceOf($method->stmts ?? [], StaticCall::class) as $call) {
            if (
                $call->class instanceof Name
                && $call->class->getLast() === 'PortalPatientAccessGuard'
                && $call->name instanceof Identifier
            ) {
                $calls[] = $call->name->toString();
            }
        }

        return $calls;
    }
}
