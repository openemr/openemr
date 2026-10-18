<?php

/**
 * FhirValueSetService Integration Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Lavoy <david@p.industries>
 * @copyright Copyright (c) 2026 P Industries
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRValueSet;
use OpenEMR\Services\FHIR\FhirValueSetService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FhirValueSetServiceTest extends TestCase
{
    private FhirValueSetService $service;

    protected function setUp(): void
    {
        $this->service = new FhirValueSetService();
    }

    /**
     * @return list<string>
     */
    private function idsOf(mixed $data): array
    {
        $this->assertIsArray($data);
        $ids = [];
        foreach ($data as $valueSet) {
            $this->assertInstanceOf(FHIRValueSet::class, $valueSet);
            $ids[] = (string) $valueSet->getId();
        }
        return $ids;
    }

    #[Test]
    public function readAppointmentType(): void
    {
        $result = $this->service->getOne('appointment-type');

        $this->assertTrue($result->isValid());
        $this->assertSame(['appointment-type'], $this->idsOf($result->getData()));
    }

    #[Test]
    public function readListBackedValueSet(): void
    {
        $result = $this->service->getOne('drug_form');

        $this->assertTrue($result->isValid());
        $this->assertSame(['drug_form'], $this->idsOf($result->getData()));
    }

    #[Test]
    public function searchWithoutIdStillReturnsEverything(): void
    {
        $result = $this->service->getAll([]);

        $this->assertTrue($result->isValid());
        $ids = $this->idsOf($result->getData());
        $this->assertContains('appointment-type', $ids);
        $this->assertContains('drug_form', $ids);
    }
}
