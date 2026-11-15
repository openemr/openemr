<?php

/**
 * A portal profile request can only reach the record it names, and its body can never change
 * which patient the record is: the controllers check access to the patient of the record loaded
 * from the chart before they apply any request field, and no request field list includes the
 * record's `pid` or `id`.
 *
 * The controllers need the Phreeze runtime, so their checks are read from source; the request
 * mapping is exercised directly.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    GitHub Copilot AI Assistant
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 OpenEMR <info@open-emr.org>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Services\PortalProfile\PortalProfileService;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PatientControllerSecurityTest extends TestCase
{
    private const CONTROLLERS = __DIR__ . '/../../../../src/Controllers/Portal/Patient/';

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function guardedMethodProvider(): array
    {
        return [
            'profile read' => ['PatientController', 'Read', 'assertCanRead'],
            'commit to chart' => ['PatientController', 'Update', 'assertCanWrite'],
            'pending change read' => ['PortalPatientController', 'Read', 'assertCanRead'],
            'profile change for review' => ['PortalPatientController', 'Update', 'assertCanWrite'],
        ];
    }

    /**
     * Each write checks access against the record as loaded, before the request changes it.
     */
    #[DataProvider('guardedMethodProvider')]
    public function testTheGuardChecksTheLoadedRecordBeforeTheRequestIsApplied(string $controller, string $method, string $check): void
    {
        $statements = $this->method($controller, $method)->stmts ?? [];
        $finder = new NodeFinder();

        $guard = $finder->findFirst($statements, static fn(Node $node): bool => $node instanceof StaticCall
            && $node->class instanceof Name && $node->class->getLast() === 'PortalPatientAccessGuard'
            && $node->name instanceof Identifier && $node->name->toString() === $check);
        $this->assertInstanceOf(StaticCall::class, $guard, "{$controller}::{$method}() calls {$check}");
        $this->assertCount(1, $guard->args);
        $argument = $guard->args[0];
        $this->assertInstanceOf(Node\Arg::class, $argument);
        $this->assertSame(
            "\$record['pid']",
            (new Standard())->prettyPrintExpr($argument->value),
            'the guard checks the patient of the record loaded from the chart',
        );
        $this->assertInstanceOf(ArrayDimFetch::class, $argument->value);

        $apply = $finder->findFirst($statements, static fn(Node $node): bool => $node instanceof StaticCall
            && $node->name instanceof Identifier && $node->name->toString() === 'applyRequest');
        if ($apply instanceof StaticCall) {
            $this->assertLessThan($apply->getStartLine(), $guard->getStartLine(), 'the guard runs before the request is applied');
        }
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fieldListProvider(): array
    {
        return [
            'registration' => ['PatientController', 'REGISTRATION_FIELDS'],
            'commit to chart' => ['PatientController', 'UPDATE_FIELDS'],
            'profile change for review' => ['PortalPatientController', 'UPDATE_FIELDS'],
        ];
    }

    /**
     * The writable field lists name only real profile fields, never pid or id.
     */
    #[DataProvider('fieldListProvider')]
    public function testNoRequestCanChooseTheRecordOrThePatient(string $controller, string $constant): void
    {
        $fields = $this->stringList($controller, $constant);

        $this->assertNotEmpty($fields);
        $this->assertNotContains('pid', $fields);
        $this->assertNotContains('id', $fields);
        foreach ($fields as $field) {
            $this->assertTrue(
                $field === 'note' || array_key_exists($field, PortalProfileService::FIELDS),
                "{$controller}::{$constant} names a real profile field: {$field}",
            );
        }
    }

    public function testApplyingARequestChangesOnlyTheListedFields(): void
    {
        $record = ['id' => '7', 'pid' => '11', 'fname' => 'Ann', 'email' => 'ann@example.com', 'dob' => '1980-02-03'];
        $body = (object) ['id' => '8', 'pid' => '22', 'fname' => 'Changed', 'email' => 'x@example.com'];

        $applied = PortalProfileService::applyRequest($record, $body, ['fname', 'dob']);

        $this->assertSame('7', $applied['id']);
        $this->assertSame('11', $applied['pid']);
        $this->assertSame('Changed', $applied['fname']);
        $this->assertSame('ann@example.com', $applied['email'], 'a field not listed keeps its value');
        $this->assertSame('1980-02-03', $applied['dob'], 'a listed field the body leaves out keeps its value');
    }

    public function testDatesAreNormalisedAsBefore(): void
    {
        $applied = PortalProfileService::applyRequest(
            ['dob' => null, 'date' => null, 'regdate' => null],
            (object) ['dob' => '03/04/1990', 'date' => '2026-11-04 09:30', 'regdate' => ''],
            ['dob', 'date', 'regdate'],
        );

        $this->assertSame('1990-03-04', $applied['dob']);
        $this->assertSame('2026-11-04 09:30:00', $applied['date']);
        $this->assertSame('1970-01-01', $applied['regdate'], 'an empty date becomes the epoch, as Phreeze saved it');
    }

    /**
     * @return list<string>
     */
    private function stringList(string $controller, string $constant): array
    {
        $const = (new NodeFinder())->findFirst(
            $this->ast($controller),
            static fn(Node $node): bool => $node instanceof ClassConst && $node->consts[0]->name->toString() === $constant,
        );
        $this->assertInstanceOf(ClassConst::class, $const, "{$controller}::{$constant} exists");
        $value = $const->consts[0]->value;
        $this->assertInstanceOf(Node\Expr\Array_::class, $value);
        $fields = [];
        foreach ($value->items as $item) {
            $this->assertInstanceOf(String_::class, $item->value);
            $fields[] = $item->value->value;
        }

        return $fields;
    }

    /**
     * The named method of a controller, parsed.
     */
    private function method(string $controller, string $name): ClassMethod
    {
        $method = (new NodeFinder())->findFirst(
            $this->ast($controller),
            static fn(Node $node): bool => $node instanceof ClassMethod && $node->name->toString() === $name,
        );
        $this->assertInstanceOf(ClassMethod::class, $method, "{$controller}::{$name}() exists");

        return $method;
    }

    /**
     * @return array<Node>
     */
    private function ast(string $controller): array
    {
        $source = file_get_contents(self::CONTROLLERS . $controller . '.php');
        $this->assertIsString($source);

        return (new ParserFactory())->createForHostVersion()->parse($source) ?? [];
    }
}
