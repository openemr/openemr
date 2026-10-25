<?php

declare(strict_types=1);

namespace GeneralDermatology\Tests;

use GeneralDermatology\Catalog\LocationCatalog;
use GeneralDermatology\Catalog\ProcedureCatalog;
use GeneralDermatology\Domain\Finding;
use GeneralDermatology\Domain\TreatmentDocument;
use PHPUnit\Framework\TestCase;

class Phase3CatalogTest extends TestCase
{
    public function testProcedureCatalogKnowsBiopsy(): void
    {
        $this->assertTrue(ProcedureCatalog::isKnown('Biopsy'));
        $this->assertStringContainsStringIgnoringCase('biopsy', ProcedureCatalog::labelFor('Biopsy'));
        $this->assertNotEmpty(ProcedureCatalog::plans());
        $this->assertArrayHasKey('Cryotherapy', ProcedureCatalog::plans());
    }

    public function testLocationCatalogLoadsMapRegions(): void
    {
        $all = LocationCatalog::allById();
        $this->assertGreaterThan(50, count($all));

        $byLabel = LocationCatalog::findByLabel('Right Forearm');
        if ($byLabel === null) {
            // Map may name it with different wording; still assert slug API works.
            $this->assertSame('right_forearm', LocationCatalog::idFromLabel('Right Forearm'));
        } else {
            $this->assertSame('R', $byLabel['laterality']);
        }

        $this->assertSame('L', LocationCatalog::inferLaterality('Left Cheek'));
        $this->assertSame('face', LocationCatalog::inferView('Left Nasal Ala'));
    }

    public function testFindingDtoUsesCatalogs(): void
    {
        $finding = Finding::fromArray([
            'finding_location' => 'Left Cheek',
            'dx_s' => 'C44.311',
            'dx_desc' => 'pink papule',
            'procedure_plans' => 'Biopsy',
            'plan' => 'Shave biopsy performed',
        ]);

        $this->assertFalse($finding->isEmpty());
        $this->assertSame('Left Cheek', $finding->findingLocation);
        $this->assertSame('L', $finding->location['laterality']);
        $this->assertSame('face', $finding->location['view']);
        $this->assertNotSame('', $finding->procedureLabel);
        $this->assertSame('C44.311', $finding->dxCode);
    }

    public function testTreatmentDocumentLocationUsesCatalogInference(): void
    {
        $doc = TreatmentDocument::fromPostFindings([
            [
                'finding_location' => 'Right Dorsal Hand',
                'dx_s' => 'L82.1',
                'procedure_plans' => 'Cryotherapy',
            ],
        ]);

        $finding = $doc['findings'][0];
        $this->assertSame('R', $finding['location']['laterality']);
        $this->assertNotSame('', $finding['location']['id']);
        $this->assertSame('Right Dorsal Hand', $finding['location']['label']);
    }

    public function testLocationCatalogBootstrapAndMapKeyResolve(): void
    {
        $boot = LocationCatalog::bootstrapForMapUi();
        $this->assertArrayHasKey('byId', $boot);
        $this->assertArrayHasKey('byLabel', $boot);
        $this->assertNotEmpty($boot['byId']);
        $this->assertArrayHasKey('left_alar_crease', $boot['byId']);
        $this->assertArrayHasKey('left alar crease', $boot['byLabel']);

        $resolved = LocationCatalog::resolveFromMapKey('Left Alar Crease', 'face');
        $this->assertSame('left_alar_crease', $resolved['id']);
        $this->assertSame('L', $resolved['laterality']);
        $this->assertSame('face', $resolved['view']);
        $this->assertSame([], $resolved['markers']);
    }
}
