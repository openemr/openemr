<?php

/**
 * Isolated ProcedureService Test
 *
 * Reflection-based tests to verify the insert() and update() API contract
 * without requiring a database connection (ProcedureService constructor
 * calls parent::__construct() which runs SQL queries).
 *
 * BaseService has a file-scope require_once for code_types.inc.php which
 * calls sqlStatement(). The bootstrap defines OPENEMR_STATIC_ANALYSIS to
 * skip those database calls at include time.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services;

require_once __DIR__ . '/ProcedureServiceBootstrap.php';

use OpenEMR\Services\ProcedureService;
use OpenEMR\Validators\ProcedureOrderValidator;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProcedureServiceTest extends TestCase
{
    private ReflectionClass $reflectionClass;

    protected function setUp(): void
    {
        $this->reflectionClass = new ReflectionClass(ProcedureService::class);
    }

    public function testInsertMethodExists(): void
    {
        $this->assertTrue(
            $this->reflectionClass->hasMethod('insert'),
            'ProcedureService must have an insert() method'
        );
    }

    public function testInsertMethodSignature(): void
    {
        $method = $this->reflectionClass->getMethod('insert');

        $this->assertTrue($method->isPublic(), 'insert() must be public');

        $params = $method->getParameters();
        $this->assertCount(1, $params, 'insert() must accept exactly 1 parameter');
        $this->assertSame('data', $params[0]->getName());
        $this->assertSame('array', $params[0]->getType()->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType, 'insert() must declare a return type');
        $this->assertSame(ProcessingResult::class, $returnType->getName());
    }

    public function testUpdateMethodExists(): void
    {
        $this->assertTrue(
            $this->reflectionClass->hasMethod('update'),
            'ProcedureService must have an update() method'
        );
    }

    public function testUpdateMethodSignature(): void
    {
        $method = $this->reflectionClass->getMethod('update');

        $this->assertTrue($method->isPublic(), 'update() must be public');

        $params = $method->getParameters();
        $this->assertCount(2, $params, 'update() must accept exactly 2 parameters');

        $this->assertSame('uuid', $params[0]->getName());
        $this->assertSame('string', $params[0]->getType()->getName());

        $this->assertSame('data', $params[1]->getName());
        $this->assertSame('array', $params[1]->getType()->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType, 'update() must declare a return type');
        $this->assertSame(ProcessingResult::class, $returnType->getName());
    }

    public function testServiceHasValidatorProperty(): void
    {
        $this->assertTrue(
            $this->reflectionClass->hasProperty('procedureOrderValidator'),
            'ProcedureService must have a procedureOrderValidator property'
        );

        $property = $this->reflectionClass->getProperty('procedureOrderValidator');
        $this->assertTrue($property->isPrivate(), 'procedureOrderValidator must be private');
        $this->assertTrue($property->isReadOnly(), 'procedureOrderValidator must be readonly');

        $type = $property->getType();
        $this->assertNotNull($type, 'procedureOrderValidator must be typed');
        $this->assertSame(ProcedureOrderValidator::class, $type->getName());
    }
}
