<?php

/*
 * NativeQuestionnaireResponseProcessorTest.php
 *
 * Unit coverage for the validation and server-side stamping guards extracted from
 * interface/forms/questionnaire_assessments/native_save.php. These are the guards protecting
 * the one endpoint that persists untrusted FHIR QuestionnaireResponse payloads, so a
 * regression to any of them should fail here.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services {

    // Stub the unqualified xlt() the processor uses for its messages, in the same namespace as
    // the code under test so PHP resolves to this before the global fallback. Guarded so the
    // isolated suite can share a process without collision.
    if (!function_exists('OpenEMR\\Services\\xlt')) {
        function xlt(string $s): string
        {
            return $s;
        }
    }
}

namespace OpenEMR\Tests\Isolated\Services {

    use OpenEMR\Services\InvalidQuestionnaireResponseException;
    use OpenEMR\Services\NativeQuestionnaireResponseProcessor;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\TestCase;

    class NativeQuestionnaireResponseProcessorTest extends TestCase
    {
        private NativeQuestionnaireResponseProcessor $processor;

        protected function setUp(): void
        {
            $this->processor = new NativeQuestionnaireResponseProcessor();
        }

        private function validResponseJson(string $status = 'completed'): string
        {
            $json = json_encode([
                'resourceType' => 'QuestionnaireResponse',
                'status' => $status,
                'item' => [],
            ]);
            $this->assertIsString($json);
            return $json;
        }

        // --- normalizeNonNegativeInt ------------------------------------------------------

        /**
         * @return array<string, array{mixed, ?int}>
         */
        public static function nonNegativeIntProvider(): array
        {
            return [
                'int zero' => [0, 0],
                'positive int' => [42, 42],
                'negative int' => [-1, null],
                'digit string' => ['7', 7],
                'zero string' => ['0', 0],
                'negative string' => ['-3', null],
                'empty string' => ['', null],
                'non-numeric string' => ['abc', null],
                'float-ish string' => ['1.5', null],
                'leading plus' => ['+5', null],
                'whitespace' => [' 5 ', null],
                'float' => [1.5, null],
                'true' => [true, null],
                'null' => [null, null],
                'array' => [[], null],
            ];
        }

        #[DataProvider('nonNegativeIntProvider')]
        public function testNormalizeNonNegativeInt(mixed $input, ?int $expected): void
        {
            $this->assertSame($expected, $this->processor->normalizeNonNegativeInt($input));
        }

        // --- decodeAndValidateResponse ----------------------------------------------------

        public function testDecodeValidResponseReturnsArray(): void
        {
            $decoded = $this->processor->decodeAndValidateResponse($this->validResponseJson());
            $this->assertSame('QuestionnaireResponse', $decoded['resourceType']);
            $this->assertSame('completed', $decoded['status']);
        }

        /**
         * @return array<string, array{string}>
         */
        public static function allowedStatusProvider(): array
        {
            return [
                'in-progress' => ['in-progress'],
                'completed' => ['completed'],
                'amended' => ['amended'],
            ];
        }

        #[DataProvider('allowedStatusProvider')]
        public function testAllowedStatusesAccepted(string $status): void
        {
            $decoded = $this->processor->decodeAndValidateResponse($this->validResponseJson($status));
            $this->assertSame($status, $decoded['status']);
        }

        public function testEmptyPayloadRejected(): void
        {
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateResponse('   ');
        }

        public function testOversizePayloadRejected(): void
        {
            // Build a syntactically valid but oversize payload.
            $filler = str_repeat('a', NativeQuestionnaireResponseProcessor::MAX_PAYLOAD_BYTES + 1);
            $json = json_encode([
                'resourceType' => 'QuestionnaireResponse',
                'status' => 'completed',
                'note' => $filler,
            ]);
            $this->assertIsString($json);
            $this->assertGreaterThan(NativeQuestionnaireResponseProcessor::MAX_PAYLOAD_BYTES, strlen($json));
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateResponse($json);
        }

        public function testMalformedJsonRejected(): void
        {
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateResponse('{"resourceType": "QuestionnaireResponse", ');
        }

        public function testNonObjectJsonRejected(): void
        {
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateResponse('[1,2,3]');
        }

        public function testWrongResourceTypeRejected(): void
        {
            $json = (string) json_encode(['resourceType' => 'Patient', 'status' => 'completed']);
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateResponse($json);
        }

        public function testMissingResourceTypeRejected(): void
        {
            $json = (string) json_encode(['status' => 'completed']);
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateResponse($json);
        }

        /**
         * @return array<string, array{mixed}>
         */
        public static function invalidStatusProvider(): array
        {
            return [
                'disallowed value' => ['entered-in-error'],
                'empty' => [''],
                'stopped' => ['stopped'],
                'uppercase' => ['COMPLETED'],
                'null' => [null],
                'numeric' => [1],
            ];
        }

        #[DataProvider('invalidStatusProvider')]
        public function testInvalidStatusRejected(mixed $status): void
        {
            $json = (string) json_encode([
                'resourceType' => 'QuestionnaireResponse',
                'status' => $status,
            ]);
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateResponse($json);
        }

        // --- decodeAndValidateQuestionnaire -----------------------------------------------

        public function testValidQuestionnaireSnapshotAccepted(): void
        {
            $json = (string) json_encode(['resourceType' => 'Questionnaire', 'url' => 'http://x/q', 'id' => 'q1']);
            $decoded = $this->processor->decodeAndValidateQuestionnaire($json);
            $this->assertSame('Questionnaire', $decoded['resourceType']);
        }

        public function testInvalidQuestionnaireSnapshotRejected(): void
        {
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateQuestionnaire('{"resourceType":"QuestionnaireResponse"}');
        }

        public function testEmptyQuestionnaireSnapshotRejected(): void
        {
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->decodeAndValidateQuestionnaire('');
        }

        // --- stampResponse ----------------------------------------------------------------

        public function testStampForcesSubjectToSessionPatient(): void
        {
            $response = [
                'resourceType' => 'QuestionnaireResponse',
                'status' => 'completed',
                'subject' => ['reference' => 'Patient/attacker-controlled'],
            ];
            $stamped = $this->processor->stampResponse($response, 'good-uuid', '2026-01-01T00:00:00+00:00');
            $this->assertIsArray($stamped['subject']);
            $this->assertSame('Patient/good-uuid', $stamped['subject']['reference']);
        }

        public function testStampStripsClientIdAndMeta(): void
        {
            $response = [
                'resourceType' => 'QuestionnaireResponse',
                'status' => 'completed',
                'id' => 'client-chosen-id',
                'meta' => ['versionId' => '99'],
            ];
            $stamped = $this->processor->stampResponse($response, 'uuid', '2026-01-01T00:00:00+00:00');
            $this->assertArrayNotHasKey('id', $stamped);
            $this->assertArrayNotHasKey('meta', $stamped);
        }

        public function testStampStripsClientQuestionnaireReference(): void
        {
            // The save service is the sole authority for the questionnaire canonical, so any
            // client-supplied reference must be removed here rather than persisted.
            $response = [
                'resourceType' => 'QuestionnaireResponse',
                'status' => 'completed',
                'questionnaire' => 'Questionnaire/client-forged|9.9',
            ];
            $stamped = $this->processor->stampResponse($response, 'uuid', '2026-01-01T00:00:00+00:00');
            $this->assertArrayNotHasKey('questionnaire', $stamped);
        }

        public function testStampStripsClientEncounterReference(): void
        {
            // This workspace persists patient-context records (encounter 0), and the save service
            // skips setEncounter() for a zero encounter, so a client-supplied encounter would
            // otherwise survive into the persisted resource and falsely claim an encounter.
            $response = [
                'resourceType' => 'QuestionnaireResponse',
                'status' => 'completed',
                'encounter' => ['reference' => 'Encounter/client-forged'],
            ];
            $stamped = $this->processor->stampResponse($response, 'uuid', '2026-01-01T00:00:00+00:00');
            $this->assertArrayNotHasKey('encounter', $stamped);
        }

        /**
         * Every server-owned field must be removed from the client payload. Fields the server
         * re-applies (subject, authored) are asserted to hold the server value, not the forged
         * one; the rest must be absent entirely. Driven by the SERVER_OWNED_FIELDS constant so a
         * newly added field is automatically covered.
         *
         * @return array<string, array{string}>
         */
        public static function serverOwnedFieldProvider(): array
        {
            $cases = [];
            foreach (NativeQuestionnaireResponseProcessor::SERVER_OWNED_FIELDS as $field) {
                $cases[$field] = [$field];
            }
            return $cases;
        }

        #[DataProvider('serverOwnedFieldProvider')]
        public function testStampNeverPersistsForgedServerOwnedField(string $field): void
        {
            // Seed a hostile value under each server-owned field.
            $response = [
                'resourceType' => 'QuestionnaireResponse',
                'status' => 'completed',
                $field => ['reference' => 'forged/value', 'FORGED' => true],
            ];
            $stamped = $this->processor->stampResponse($response, 'session-patient-uuid', '2026-01-01T00:00:00+00:00');

            if ($field === 'subject') {
                $this->assertSame(['reference' => 'Patient/session-patient-uuid'], $stamped['subject']);
            } elseif ($field === 'authored') {
                $this->assertSame('2026-01-01T00:00:00+00:00', $stamped['authored']);
            } else {
                $this->assertArrayNotHasKey($field, $stamped, "Server-owned field '$field' must not survive from the client payload.");
            }
        }

        public function testStampSetsAuthoredWhenProvided(): void
        {
            $stamped = $this->processor->stampResponse(
                ['resourceType' => 'QuestionnaireResponse', 'status' => 'completed'],
                'uuid',
                '2026-07-27T12:00:00+00:00'
            );
            $this->assertSame('2026-07-27T12:00:00+00:00', $stamped['authored']);
        }

        public function testStampDefaultsAuthoredToNow(): void
        {
            $stamped = $this->processor->stampResponse(
                ['resourceType' => 'QuestionnaireResponse', 'status' => 'completed'],
                'uuid'
            );
            $this->assertArrayHasKey('authored', $stamped);
            $this->assertIsString($stamped['authored']);
            // ISO-8601 with timezone offset, e.g. 2026-07-27T12:00:00+00:00
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
                $stamped['authored']
            );
        }

        public function testStampRejectsEmptyPatientUuid(): void
        {
            $this->expectException(InvalidQuestionnaireResponseException::class);
            $this->processor->stampResponse(
                ['resourceType' => 'QuestionnaireResponse', 'status' => 'completed'],
                ''
            );
        }
    }
}
