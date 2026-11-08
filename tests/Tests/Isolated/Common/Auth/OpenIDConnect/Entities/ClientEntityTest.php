<?php

/**
 * Handles unit tests of the ClientEntity
 *
 * @package OpenEMR\RestControllers\SMART
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClientEntityTest extends TestCase
{
    /**
     * @return array<string, array{list<mixed>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fhirWriteScopesProvider(): array
    {
        return [
            'no scopes' => [[], []],
            'FHIR read only' => [['api:fhir', 'user/Patient.read', 'patient/Observation.rs'], []],
            'legacy write' => [['api:fhir', 'user/Patient.write'], ['user/Patient.write']],
            'create' => [['api:fhir', 'user/Patient.c'], ['user/Patient.c']],
            'update' => [['api:fhir', 'user/Patient.u'], ['user/Patient.u']],
            'delete' => [['api:fhir', 'user/Patient.d'], ['user/Patient.d']],
            'full CRUDS' => [['api:fhir', 'user/Patient.cruds'], ['user/Patient.cruds']],
            'patient write' => [['api:fhir', 'patient/QuestionnaireResponse.cu'], ['patient/QuestionnaireResponse.cu']],
            'system write' => [['api:fhir', 'system/Observation.cud'], ['system/Observation.cud']],
            'wildcard write' => [['api:fhir', 'user/*.write', 'system/*.cud'], ['user/*.write', 'system/*.cud']],
            'constrained write' => [['api:fhir', 'user/Observation.cu?category=laboratory'], ['user/Observation.cu?category=laboratory']],
            'read constraint containing write' => [['api:fhir', 'user/Observation.rs?code=write'], []],
            'retrieval operations' => [['api:fhir', 'patient/DocumentReference.$docref', 'system/*.$export'], []],
            'standard API only' => [['api:oemr', 'user/patient.write'], []],
            'standard client wildcard includes FHIR resources' => [['api:oemr', 'user/*.write'], ['user/*.write']],
            'mixed APIs with standard write' => [['api:fhir', 'api:oemr', 'user/patient.write'], []],
            'portal API only' => [['api:port', 'patient/patient.write'], []],
            'legacy write without FHIR API scope' => [['user/Patient.write'], ['user/Patient.write']],
            'SMART v2 writes without FHIR API scope' => [['patient/QuestionnaireResponse.cu', 'system/Observation.d'], ['patient/QuestionnaireResponse.cu', 'system/Observation.d']],
            'wildcard writes without FHIR API scope' => [['user/*.write', 'system/*.cud'], ['user/*.write', 'system/*.cud']],
            'read only without FHIR API scope' => [['user/Patient.read', 'patient/Observation.rs'], []],
            'write scope after preview limit' => [['openid', 'offline_access', 'launch', 'api:fhir', 'user/Patient.read', 'user/Observation.rs', 'user/Patient.write'], ['user/Patient.write']],
            'malformed stored scopes' => [['api:fhir', 'user/Patient.ur', 'user/Patient.bogus', 'system:Patient.write', null, 7, 'user/Patient.write'], ['user/Patient.write']],
        ];
    }

    /**
     * @param list<mixed> $scopes
     * @param list<string> $expected
     */
    #[DataProvider('fhirWriteScopesProvider')]
    public function testFhirWriteScopes(array $scopes, array $expected): void
    {
        $client = new ClientEntity();
        $client->setScopes($scopes);

        $this->assertSame($expected, $client->getFhirWriteScopes());
        $this->assertSame($scopes, $client->getScopes());
        $this->assertFalse($client->isEnabled());
    }

    /**
     * Checks to make sure the hasScope method is working properly
     */
    public function testHasScope(): void
    {
        $client = new ClientEntity();
        $client->setScopes('openid email phone address launch api:oemr api:fhir api:port');

        $this->assertFalse($client->hasScope("bacon"), "invalid scope should not return true");
        $this->assertTrue($client->hasScope("launch"), "launch scope should have been found");
        $this->assertFalse($client->hasScope("launch/patient"), "scope should not match against a prefix");
    }
}
