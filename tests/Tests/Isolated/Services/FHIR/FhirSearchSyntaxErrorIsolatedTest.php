<?php

/**
 * A FHIR search whose parameter value doesn't parse is marked, and answered with 400 and an OperationOutcome;
 * an unknown parameter, a value that can't be satisfied, and a refused patient scope keep today's empty result.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\Traits {

    // An unknown parameter's message goes through the unqualified xlt(), which needs the database. Stub it in the
    // trait's namespace, so PHP finds it before the global function; guarded so the isolated suite can share a process.
    if (!function_exists('OpenEMR\\Services\\FHIR\\Traits\\xlt')) {
        function xlt(string $s): string
        {
            return $s;
        }
    }
}

namespace OpenEMR\Tests\Isolated\Services\FHIR {

    use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
    use OpenEMR\RestControllers\RestControllerHelper;
    use OpenEMR\Services\FHIR\FhirSearchSyntaxError;
    use OpenEMR\Services\FHIR\FhirServiceBase;
    use OpenEMR\Services\Search\FhirSearchParameterDefinition;
    use OpenEMR\Services\Search\SearchFieldException;
    use OpenEMR\Services\Search\SearchFieldType;
    use OpenEMR\Services\Search\SearchParameterSyntaxException;
    use OpenEMR\Services\Search\ServiceField;
    use OpenEMR\Validators\ProcessingResult;
    use PHPUnit\Framework\TestCase;

    class FhirSearchSyntaxErrorIsolatedTest extends TestCase
    {
        public function testAMalformedDateIsMarkedAsTheParametersSyntaxError(): void
        {
            $service = new SearchSyntaxTestService();
            $result = $service->getAll(['birthdate' => 'not-a-date']);

            $this->assertFalse($service->searchWasReached);
            $this->assertSame('birthdate', FhirSearchSyntaxError::find($result));
            // The parser's message stays in the validation messages, which the server logs and never sends.
            $messages = $result->getValidationMessages();
            $this->assertIsArray($messages);
            $this->assertIsString($messages['birthdate'] ?? null);
            $this->assertStringContainsString('ISO8601', $messages['birthdate']);
        }

        public function testADateWithAnUnknownComparatorIsASyntaxError(): void
        {
            $result = (new SearchSyntaxTestService())->getAll(['birthdate' => 'xx2020-01-01']);

            $this->assertSame('birthdate', FhirSearchSyntaxError::find($result));
        }

        public function testAWellFormedDateSearchesAsBefore(): void
        {
            $service = new SearchSyntaxTestService();
            $result = $service->getAll(['birthdate' => 'ge2020-01-01']);

            $this->assertTrue($service->searchWasReached);
            $this->assertTrue($result->isValid());
            $this->assertNull(FhirSearchSyntaxError::find($result));
        }

        /**
         * R4 search 3.1.1.3: an id that can't exist is a logical condition, answered with an empty set.
         */
        public function testAnIdThatIsNotAUuidKeepsTheEmptyResult(): void
        {
            $result = (new SearchSyntaxTestService())->getAll(['_id' => 'not-a-uuid']);

            $this->assertFalse($result->isValid());
            $this->assertNull(FhirSearchSyntaxError::find($result));
        }

        public function testAnUnknownParameterKeepsTheEmptyResult(): void
        {
            $result = (new SearchSyntaxTestService())->getAll(['nosuchparam' => '1']);

            $this->assertFalse($result->isValid());
            $this->assertNull(FhirSearchSyntaxError::find($result));
        }

        public function testARefusedPatientScopeIsNotTurnedIntoASyntaxError(): void
        {
            $result = (new SearchSyntaxTestService())->getAll(['birthdate' => 'ge2020-01-01'], 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');

            $this->assertFalse($result->isValid());
            $this->assertNull(FhirSearchSyntaxError::find($result));
        }

        public function testRecordKeepsTheMessageTheServicesAlwaysRecorded(): void
        {
            $plain = new ProcessingResult();
            FhirSearchSyntaxError::record($plain, new SearchFieldException('code', 'Invalid code'));
            $this->assertSame(['code' => 'Invalid code'], $plain->getValidationMessages());

            $syntax = new ProcessingResult();
            FhirSearchSyntaxError::record($syntax, new SearchParameterSyntaxException('date', 'Invalid ISO8601 date'));
            $this->assertSame(
                ['date' => 'Invalid ISO8601 date', FhirSearchSyntaxError::VALIDATION_KEY => 'date'],
                $syntax->getValidationMessages()
            );
        }

        /**
         * A mapped service (Observation, Condition...) combines its sub-services' results into a new one.
         */
        public function testTheMarkSurvivesAMappedServiceCombiningResults(): void
        {
            $inner = new ProcessingResult();
            FhirSearchSyntaxError::record($inner, new SearchParameterSyntaxException('date', 'Invalid ISO8601 date'));
            $combined = new ProcessingResult();
            $combined->addProcessingResult($inner);
            $combined->clearData();

            $this->assertSame('date', FhirSearchSyntaxError::find($combined));
        }

        public function testTheResponseIsA400OperationOutcomeNamingTheParameter(): void
        {
            $result = (new SearchSyntaxTestService())->getAll(['birthdate' => 'not-a-date']);

            $response = RestControllerHelper::getFhirSearchErrorResponse($result);
            $this->assertNotNull($response);
            $this->assertSame(400, $response->getStatusCode());
            $body = json_decode((string) $response->getContent(), true);
            $this->assertIsArray($body);
            $this->assertSame('OperationOutcome', $body['resourceType'] ?? null);
            $this->assertIsArray($body['issue'] ?? null);
            $this->assertCount(1, $body['issue']);
            $issue = $body['issue'][0];
            $this->assertIsArray($issue);
            $this->assertSame('error', $issue['severity'] ?? null);
            $this->assertSame('invalid', $issue['code'] ?? null);
            $this->assertSame(
                "Search parameter 'birthdate' must be a FHIR date or dateTime, such as 2020-01-31 or 2020-01-31T13:45:00Z.",
                $issue['diagnostics'] ?? null
            );
            // The parser's message, which the search trait wraps as "Inner message: ...", isn't sent to the client.
            $this->assertStringNotContainsString('Inner message', (string) $response->getContent());

            $psr = RestControllerHelper::getFhirSearchErrorPsrResponse($result);
            $this->assertNotNull($psr);
            $this->assertSame(400, $psr->getStatusCode());
            $this->assertSame($body, json_decode((string) $psr->getBody(), true));
        }

        public function testThereIsNoErrorResponseForAnyOtherResult(): void
        {
            $service = new SearchSyntaxTestService();
            foreach ([['birthdate' => 'ge2020-01-01'], ['nosuchparam' => '1'], ['_id' => 'not-a-uuid']] as $search) {
                $result = $service->getAll($search);
                $this->assertNull(RestControllerHelper::getFhirSearchErrorResponse($result));
                $this->assertNull(RestControllerHelper::getFhirSearchErrorPsrResponse($result));
            }
            // A controller whose service property is untyped hands over whatever its service returned.
            $this->assertNull(RestControllerHelper::getFhirSearchErrorResponse(null));
            $this->assertNull(RestControllerHelper::getFhirSearchErrorPsrResponse(null));
        }

        public function testTheSearchTraitKeepsTheSyntaxErrorUnderTheRequestsParameterName(): void
        {
            try {
                (new SearchSyntaxTestService())->searchFields(['birthdate' => 'not-a-date']);
                $this->fail('A malformed date is rejected');
            } catch (SearchParameterSyntaxException $exception) {
                $this->assertSame('birthdate', $exception->getField());
            }
        }

        /**
         * The trait re-wraps every InvalidArgumentException, and SearchFieldException is one; an unknown
         * parameter must come out of that as a plain SearchFieldException.
         */
        public function testTheSearchTraitLeavesAnUnknownParameterAPlainSearchFieldException(): void
        {
            try {
                (new SearchSyntaxTestService())->searchFields(['nosuchparam' => '1']);
                $this->fail('An unknown parameter is rejected');
            } catch (SearchFieldException $exception) {
                $this->assertNotInstanceOf(SearchParameterSyntaxException::class, $exception);
                $this->assertSame('nosuchparam', $exception->getField());
            }
        }
    }

    /**
     * @internal A FhirServiceBase with one date and one uuid search parameter, and no records.
     */
    final class SearchSyntaxTestService extends FhirServiceBase
    {
        public bool $searchWasReached = false;

        /**
         * @param array<string, mixed> $fhirSearchParameters
         * @return array<mixed>
         */
        public function searchFields(array $fhirSearchParameters): array
        {
            return $this->createOpenEMRSearchParameters($fhirSearchParameters);
        }

        /**
         * @return array<string, FhirSearchParameterDefinition>
         */
        protected function loadSearchParameters(): array
        {
            return [
                '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
                'birthdate' => new FhirSearchParameterDefinition('birthdate', SearchFieldType::DATE, ['DOB']),
            ];
        }

        /**
         * @return array<string, mixed>
         */
        public function parseFhirResource(FHIRDomainResource $fhirResource): array
        {
            return [];
        }

        /**
         * @param array<mixed> $dataRecord
         * @param bool $encode
         */
        public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRDomainResource
        {
            return new FHIRDomainResource();
        }

        /**
         * @param mixed $openEmrRecord
         */
        protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
        {
            return new ProcessingResult();
        }

        /**
         * @param string $fhirResourceId
         * @param array<mixed> $updatedOpenEMRRecord
         */
        protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
        {
            return new ProcessingResult();
        }

        /**
         * @param array<mixed> $openEMRSearchParameters
         */
        protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
        {
            $this->searchWasReached = true;
            return new ProcessingResult();
        }

        /**
         * @param mixed $dataRecord
         * @param bool $encode
         */
        public function createProvenanceResource($dataRecord, $encode = false): null
        {
            return null;
        }
    }
}
