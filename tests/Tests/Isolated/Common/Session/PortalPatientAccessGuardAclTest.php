<?php

/**
 * Each PortalPatientAccessGuard check asks staff for the right `patients` ACL and access type.
 * The staff branch calls AclMain, which needs the database, so this reads the guard's source.
 * The decision itself (portal ACL and patient ACL both required) is covered by the
 * isStaffAccessAllowed() tests in PortalPatientAccessGuardTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PortalPatientAccessGuardAclTest extends TestCase
{
    private const GUARD = __DIR__ . '/../../../../../src/Common/Session/PortalPatientAccessGuard.php';

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function checkAclProvider(): array
    {
        return [
            'read demographics' => ['assertCanRead', "'demo'", "''"],
            'write demographics' => ['assertCanWrite', "'demo'", "'write'"],
            'read documents' => ['assertCanReadDocuments', "'docs'", "''"],
            'add a document' => ['assertCanAddDocument', "'docs'", "['write', 'addonly']"],
            'change documents' => ['assertCanWriteDocuments', "'docs'", "'write'"],
        ];
    }

    #[DataProvider('checkAclProvider')]
    public function testCheckAsksStaffForTheRightPatientAcl(string $check, string $acl, string $accessType): void
    {
        $calls = (new NodeFinder())->findInstanceOf($this->method($check)->stmts ?? [], StaticCall::class);
        $this->assertCount(1, $calls, "{$check}() makes one call");
        $call = $calls[0];
        $this->assertInstanceOf(Name::class, $call->class);
        $this->assertSame('self', $call->class->toString());
        $this->assertInstanceOf(Identifier::class, $call->name);
        $this->assertSame('assertAccess', $call->name->toString());

        $printer = new Standard();
        $arguments = [];
        foreach ($call->args as $argument) {
            $this->assertInstanceOf(Arg::class, $argument);
            $arguments[] = $printer->prettyPrintExpr($argument->value);
        }

        $this->assertSame('$patientPid', $arguments[0] ?? null, 'checks the patient it was given');
        $this->assertSame($acl, $arguments[1] ?? null, 'patients ACL');
        $this->assertSame($accessType, $arguments[2] ?? null, 'access type');
    }

    public function testStaffNeedThePortalAclAndThePatientAcl(): void
    {
        $printer = new Standard();
        $aclChecks = [];
        foreach ((new NodeFinder())->findInstanceOf($this->method('assertAccess')->stmts ?? [], StaticCall::class) as $call) {
            if (
                $call->class instanceof Name
                && $call->class->getLast() === 'AclMain'
                && $call->name instanceof Identifier
                && $call->name->toString() === 'aclCheckCore'
            ) {
                $arguments = [];
                foreach ($call->args as $argument) {
                    $this->assertInstanceOf(Arg::class, $argument);
                    $arguments[] = $printer->prettyPrintExpr($argument->value);
                }
                $aclChecks[] = implode(', ', $arguments);
            }
        }

        $this->assertSame(
            ["'patientportal', 'portal'", "'patients', \$patientsAcl, '', \$accessType"],
            $aclChecks,
        );
    }

    private function method(string $name): ClassMethod
    {
        $source = file_get_contents(self::GUARD);
        $this->assertIsString($source);
        $ast = (new ParserFactory())->createForHostVersion()->parse($source) ?? [];
        $method = (new NodeFinder())->findFirst(
            $ast,
            static fn(Node $node): bool => $node instanceof ClassMethod && $node->name->toString() === $name,
        );
        $this->assertInstanceOf(ClassMethod::class, $method, "PortalPatientAccessGuard::{$name}() exists");

        return $method;
    }
}
