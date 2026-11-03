<?php

/**
 * Isolated tests for how FhirConditionService::parseFhirResource() reads
 * onset/abatement dates, whether they arrive as raw strings or typed elements.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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

        $this->assertArrayHasKey('begdate', $data);
        $this->assertArrayHasKey('enddate', $data);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $data['begdate']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $data['enddate']);
    }

    public function testRawStringDatesAreStillParsed(): void
    {
        $condition = new FHIRCondition();
        $condition->setOnsetDateTime('2024-03-05');
        $condition->setAbatementDateTime('2024-04-06');

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
