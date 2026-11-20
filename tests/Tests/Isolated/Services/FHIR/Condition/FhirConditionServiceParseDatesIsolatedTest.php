<?php

/**
 * Isolated tests for how FhirConditionService::parseFhirResource() reads
 * onset/abatement dates, whether they arrive as raw strings or typed elements.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Condition;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\Services\FHIR\FhirConditionService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class FhirConditionServiceParseDatesIsolatedTest extends TestCase
{
    private string $originalTimezone;

    protected function setUp(): void
    {
        // toDbDate() converts to the server timezone, so pin it to make the expected dates exact.
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
    }

    /**
     * @return array<mixed>
     */
    private function parse(FHIRCondition $condition): array
    {
        // parseFhirResource() does not touch instance state, so skip the DB-backed constructor.
        $service = (new \ReflectionClass(FhirConditionService::class))->newInstanceWithoutConstructor();
        return $service->parseFhirResource($condition);
    }

    public function testTypedDateTimeElementsAreParsed(): void
    {
        $condition = new FHIRCondition();
        $condition->setOnsetDateTime(new FHIRDateTime('2024-03-05T10:00:00+00:00'));
        $condition->setAbatementDateTime(new FHIRDateTime('2024-04-06T10:00:00+00:00'));

        $data = $this->parse($condition);

        $this->assertSame('2024-03-05', $data['begdate'] ?? null);
        $this->assertSame('2024-04-06', $data['enddate'] ?? null);
    }

    public function testRawStringDatesAreStillParsed(): void
    {
        // Client JSON reaches the parser through the constructor, which keeps the raw strings.
        $condition = new FHIRCondition([
            'onsetDateTime' => '2024-03-05',
            'abatementDateTime' => '2024-04-06',
        ]);

        $data = $this->parse($condition);

        $this->assertSame('2024-03-05', $data['begdate']);
        $this->assertSame('2024-04-06', $data['enddate']);
    }

    public function testMissingDatesAreOmitted(): void
    {
        $data = $this->parse(new FHIRCondition());

        $this->assertArrayNotHasKey('begdate', $data);
        $this->assertArrayNotHasKey('enddate', $data);
    }
}
