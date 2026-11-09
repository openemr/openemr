<?php

/**
 * Isolated tests for the participant period FhirCareTeamService builds from a provider's
 * provider_since DATE: a typed FHIRDateTime that keeps the date-only precision.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCareTeam;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCareTeam\FHIRCareTeamParticipant;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirCareTeamService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class FhirCareTeamProviderPeriodIsolatedTest extends TestCase
{
    /**
     * @param array<string, mixed> $provider
     */
    private function participant(array $provider): FHIRCareTeamParticipant
    {
        $provider += [
            'provider_uuid' => 'provider-uuid-001',
            'role' => 'physician',
            'role_title' => 'Attending Physician',
            'physician_type_codes' => null,
            'facility_uuid' => null,
        ];
        $careTeam = new FHIRCareTeam();
        // populateProviderTeamMembers() does not touch instance state, so skip the constructor;
        // a mapped role without physician_type_codes never calls the code types service.
        $service = (new \ReflectionClass(FhirCareTeamService::class))->newInstanceWithoutConstructor();
        $service->populateProviderTeamMembers(
            $careTeam,
            ['providers' => [$provider]],
            $this->createStub(CodeTypesService::class)
        );

        $participants = $careTeam->getParticipant();
        $this->assertCount(1, $participants);
        return $participants[0];
    }

    public function testProviderSinceIsADateOnlyDateTime(): void
    {
        $participant = $this->participant(['provider_since' => '2024-01-15']);

        // getValue() exists only on a typed FHIRDateTime; a raw string here would throw.
        $this->assertSame('2024-01-15', $participant->getPeriod()->getStart()->getValue());
        $this->assertSame(['start' => '2024-01-15'], $this->toArray($participant)['period'] ?? null);
    }

    public function testNoPeriodWithoutProviderSince(): void
    {
        $this->assertArrayNotHasKey('period', $this->toArray($this->participant(['provider_since' => null])));
        $this->assertArrayNotHasKey('period', $this->toArray($this->participant(['provider_since' => ''])));
    }

    /**
     * @return array<mixed>
     */
    private function toArray(FHIRCareTeamParticipant $participant): array
    {
        $decoded = json_decode((string) json_encode($participant), true);
        $this->assertIsArray($decoded);
        return $decoded;
    }
}
