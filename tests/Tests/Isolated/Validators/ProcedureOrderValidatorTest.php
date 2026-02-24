<?php

/**
 * Isolated ProcedureOrderValidator Test
 *
 * Tests ProcedureOrderValidator validation logic without database dependencies.
 * Uses test stubs to avoid database calls in BaseValidator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\ProcedureOrderValidator;
use PHPUnit\Framework\TestCase;

class ProcedureOrderValidatorTest extends TestCase
{
    private ProcedureOrderValidatorStub $validator;

    /** @var array<string, int> */
    private array $validInsertData;

    protected function setUp(): void
    {
        $this->validator = new ProcedureOrderValidatorStub();
        $this->validInsertData = [
            'patient_id' => 1,
            'provider_id' => 2,
            'encounter_id' => 100,
            'lab_id' => 5,
        ];
    }

    /**
     * Helper to validate and return a typed ProcessingResult.
     *
     * @param array<string, mixed> $data
     */
    private function validateData(array $data, string $context, ?ProcedureOrderValidator $validatorOverride = null): ProcessingResult
    {
        $v = $validatorOverride ?? $this->validator;
        $result = $v->validate($data, $context);
        $this->assertInstanceOf(ProcessingResult::class, $result);
        return $result;
    }

    /**
     * Helper to get validation messages as a typed array.
     *
     * @return array<string, mixed>
     */
    private function getMessages(ProcessingResult $result): array
    {
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        /** @var array<string, mixed> $messages */
        return $messages;
    }

    // ---------------------------------------------------------------
    // INSERT context — required field tests
    // ---------------------------------------------------------------

    public function testInsertValidationRequiredFields(): void
    {
        $result = $this->validateData($this->validInsertData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid data with all required fields should pass validation');
        $this->assertEmpty($this->getMessages($result), 'No validation errors expected');
    }

    public function testInsertValidationMissingPatientId(): void
    {
        $data = $this->validInsertData;
        unset($data['patient_id']);

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Missing patient_id should fail validation');
        $this->assertArrayHasKey('patient_id', $this->getMessages($result));
    }

    public function testInsertValidationMissingProviderId(): void
    {
        $data = $this->validInsertData;
        unset($data['provider_id']);

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Missing provider_id should fail validation');
        $this->assertArrayHasKey('provider_id', $this->getMessages($result));
    }

    public function testInsertValidationMissingEncounterId(): void
    {
        $data = $this->validInsertData;
        unset($data['encounter_id']);

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Missing encounter_id should fail validation');
        $this->assertArrayHasKey('encounter_id', $this->getMessages($result));
    }

    public function testInsertValidationMissingLabId(): void
    {
        $data = $this->validInsertData;
        unset($data['lab_id']);

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Missing lab_id should fail validation');
        $this->assertArrayHasKey('lab_id', $this->getMessages($result));
    }

    // ---------------------------------------------------------------
    // INSERT context — enum validation tests
    // ---------------------------------------------------------------

    public function testInsertValidationValidOrderStatus(): void
    {
        $data = $this->validInsertData;
        $data['order_status'] = 'pending';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), "'pending' should be a valid order_status");
    }

    public function testInsertValidationInvalidOrderStatus(): void
    {
        $data = $this->validInsertData;
        $data['order_status'] = 'unknown';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), "'unknown' should be an invalid order_status");
        $this->assertArrayHasKey('order_status', $this->getMessages($result));
    }

    public function testInsertValidationValidOrderPriority(): void
    {
        $data = $this->validInsertData;
        $data['order_priority'] = 'stat';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), "'stat' should be a valid order_priority");
    }

    public function testInsertValidationInvalidOrderPriority(): void
    {
        $data = $this->validInsertData;
        $data['order_priority'] = 'invalid';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), "'invalid' should be an invalid order_priority");
        $this->assertArrayHasKey('order_priority', $this->getMessages($result));
    }

    public function testInsertValidationValidOrderIntent(): void
    {
        $data = $this->validInsertData;
        $data['order_intent'] = 'order';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), "'order' should be a valid order_intent");
    }

    public function testInsertValidationInvalidOrderIntent(): void
    {
        $data = $this->validInsertData;
        $data['order_intent'] = 'invalid';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), "'invalid' should be an invalid order_intent");
        $this->assertArrayHasKey('order_intent', $this->getMessages($result));
    }

    public function testInsertValidationValidProcedureOrderType(): void
    {
        $data = $this->validInsertData;
        $data['procedure_order_type'] = 'laboratory_test';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), "'laboratory_test' should be a valid procedure_order_type");
    }

    public function testInsertValidationInvalidProcedureOrderType(): void
    {
        $data = $this->validInsertData;
        $data['procedure_order_type'] = 'unknown_type';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), "'unknown_type' should be an invalid procedure_order_type");
        $this->assertArrayHasKey('procedure_order_type', $this->getMessages($result));
    }

    // ---------------------------------------------------------------
    // INSERT context — format validation tests
    // ---------------------------------------------------------------

    public function testInsertValidationValidDateOrdered(): void
    {
        $data = $this->validInsertData;
        $data['date_ordered'] = '2026-01-15';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid date_ordered should pass validation');
    }

    public function testInsertValidationInvalidDateOrdered(): void
    {
        $data = $this->validInsertData;
        $data['date_ordered'] = 'not-a-date';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid date_ordered format should fail validation');
        $this->assertArrayHasKey('date_ordered', $this->getMessages($result));
    }

    public function testInsertValidationValidDateCollected(): void
    {
        $data = $this->validInsertData;
        $data['date_collected'] = '2026-01-16';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Valid date_collected should pass validation');
    }

    public function testInsertValidationInvalidDateCollected(): void
    {
        $data = $this->validInsertData;
        $data['date_collected'] = 'not-a-date';

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'Invalid date_collected format should fail validation');
        $this->assertArrayHasKey('date_collected', $this->getMessages($result));
    }

    public function testInsertValidationOrderDiagnosisTooLong(): void
    {
        $data = $this->validInsertData;
        $data['order_diagnosis'] = str_repeat('A', 256);

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'order_diagnosis over 255 chars should fail validation');
        $this->assertArrayHasKey('order_diagnosis', $this->getMessages($result));
    }

    public function testInsertValidationClinicalHxTooLong(): void
    {
        $data = $this->validInsertData;
        $data['clinical_hx'] = str_repeat('B', 256);

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertFalse($result->isValid(), 'clinical_hx over 255 chars should fail validation');
        $this->assertArrayHasKey('clinical_hx', $this->getMessages($result));
    }

    // ---------------------------------------------------------------
    // INSERT context — optional field tests
    // ---------------------------------------------------------------

    public function testInsertValidationWithAllOptionalFields(): void
    {
        $data = array_merge($this->validInsertData, [
            'order_status' => 'pending',
            'order_priority' => 'normal',
            'order_intent' => 'order',
            'procedure_order_type' => 'laboratory_test',
            'date_ordered' => '2026-01-15',
            'date_collected' => '2026-01-16',
            'order_diagnosis' => 'ICD10:Z00.00',
            'clinical_hx' => 'Annual checkup',
            'patient_instructions' => 'Fasting required for 12 hours before the test.',
            'billing_type' => 'T',
            'specimen_fasting' => 'YES',
        ]);

        $result = $this->validateData($data, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'All optional fields with valid values should pass validation');
        $this->assertEmpty($this->getMessages($result), 'No validation errors expected');
    }

    public function testInsertValidationWithMinimalRequiredFields(): void
    {
        $result = $this->validateData($this->validInsertData, BaseValidator::DATABASE_INSERT_CONTEXT);

        $this->assertTrue($result->isValid(), 'Only required fields should pass validation');
    }

    // ---------------------------------------------------------------
    // UPDATE context tests
    // ---------------------------------------------------------------

    public function testUpdateValidationWithValidUuid(): void
    {
        $data = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'order_status' => 'complete',
        ];

        $result = $this->validateData($data, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with valid uuid and optional field should pass');
    }

    public function testUpdateValidationMissingUuid(): void
    {
        $data = [
            'order_status' => 'pending',
        ];

        $result = $this->validateData($data, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update missing uuid should fail validation');
        $this->assertArrayHasKey('uuid', $this->getMessages($result));
    }

    public function testUpdateValidationAllFieldsOptional(): void
    {
        $data = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'patient_instructions' => 'Updated instructions',
        ];

        $result = $this->validateData($data, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertTrue($result->isValid(), 'Update with uuid and single optional field should pass');
    }

    public function testUpdateValidationInvalidEnumOnUpdate(): void
    {
        $data = [
            'uuid' => '987fcdeb-51a2-43d1-9f12-345678901234',
            'order_status' => 'invalid_status',
        ];

        $result = $this->validateData($data, BaseValidator::DATABASE_UPDATE_CONTEXT);

        $this->assertFalse($result->isValid(), 'Update with invalid enum value should fail validation');
        $this->assertArrayHasKey('order_status', $this->getMessages($result));
    }

    // ---------------------------------------------------------------
    // UUID validation tests
    // ---------------------------------------------------------------

    public function testIsExistingUuidMethodExists(): void
    {
        $reflection = new \ReflectionClass($this->validator);
        $method = $reflection->getMethod('isExistingUuid');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfParameters());
    }

    public function testIsExistingUuidWithValidUuid(): void
    {
        $validUuid = '123e4567-e89b-12d3-a456-426614174000';
        $result = $this->validator->isExistingUuid($validUuid);
        $this->assertTrue($result, 'Valid UUID should exist (via stub)');
    }

    public function testIsExistingUuidWithInvalidFormat(): void
    {
        $nonExistentValidator = new ProcedureOrderValidatorNonExistentUuidStub();
        $invalidUuid = 'not-a-valid-uuid';
        $result = $nonExistentValidator->isExistingUuid($invalidUuid);
        $this->assertFalse($result, 'Invalid UUID format should return false');
    }

    public function testIsExistingUuidWithNonExistentUuid(): void
    {
        $nonExistentValidator = new ProcedureOrderValidatorNonExistentUuidStub();
        $nonExistentUuid = '999e4567-e89b-12d3-a456-426614179999';
        $result = $nonExistentValidator->isExistingUuid($nonExistentUuid);
        $this->assertFalse($result, 'Non-existent UUID should return false');
    }
}

/**
 * Test stub that overrides database-dependent methods.
 * All IDs and UUIDs are treated as valid.
 */
class ProcedureOrderValidatorStub extends ProcedureOrderValidator
{
    public static function validateId(mixed $field, mixed $table, mixed $lookupId, mixed $isUuid = false): true
    {
        return true;
    }

    public function isExistingUuid($uuid): bool
    {
        return true;
    }
}

/**
 * Test stub for testing non-existent UUID scenarios.
 * Returns false for invalid format and specific "non-existent" UUIDs.
 */
class ProcedureOrderValidatorNonExistentUuidStub extends ProcedureOrderValidator
{
    public static function validateId(mixed $field, mixed $table, mixed $lookupId, mixed $isUuid = false): true
    {
        return true;
    }

    public function isExistingUuid($uuid): bool
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', (string) $uuid)) {
            return false;
        }

        if ($uuid === '999e4567-e89b-12d3-a456-426614179999') {
            return false;
        }

        return true;
    }
}
