<?php

declare(strict_types=1);

namespace GeneralDermatology\Tests;

use GeneralDermatology\Domain\TreatmentDocument;
use PHPUnit\Framework\TestCase;

class TreatmentDocumentTest extends TestCase
{
    public function testFromStorageMigratesLegacyV1Array(): void
    {
        $legacy = json_encode([
            [
                'finding_location' => 'Right Forearm',
                'dx_s' => 'L82.1',
                'procedure_plans' => 'Biopsy',
                'biopsy-shave' => '1',
            ],
            [
                'finding_location' => '',
                'dx_s' => 'ignored',
            ],
        ], JSON_THROW_ON_ERROR);

        $doc = TreatmentDocument::fromStorage($legacy);

        $this->assertSame(2, $doc['schema_version']);
        $this->assertCount(1, $doc['findings']);
        $this->assertSame('Right Forearm', $doc['findings'][0]['finding_location']);
        $this->assertSame('right_forearm', $doc['findings'][0]['location']['id']);
        $this->assertSame('Right Forearm', $doc['findings'][0]['location']['label']);
        $this->assertSame('1', $doc['findings'][0]['biopsy-shave']);
    }

    public function testFromStorageReadsV2Document(): void
    {
        $raw = [
            'schema_version' => 2,
            'findings' => [
                [
                    'finding_location' => 'Left Cheek',
                    'location' => [
                        'id' => 'left_cheek',
                        'label' => 'Left Cheek',
                        'laterality' => 'L',
                        'view' => 'face',
                        'markers' => [['x' => 0.4, 'y' => 0.5]],
                    ],
                    'dx_s' => 'C44.311',
                ],
            ],
        ];

        $doc = TreatmentDocument::fromStorage($raw);
        $this->assertSame(2, $doc['schema_version']);
        $this->assertSame('left_cheek', $doc['findings'][0]['location']['id']);
        $this->assertSame([['x' => 0.4, 'y' => 0.5]], $doc['findings'][0]['location']['markers']);
    }

    public function testEncodeAndFindingsForUiRoundTrip(): void
    {
        $post = [
            1 => [
                'finding_location' => 'Chin',
                'dx_s' => 'L82.0',
                'dx_desc' => ['warty papule'],
                'procedure_plans' => 'Cryotherapy',
            ],
        ];

        $doc = TreatmentDocument::fromPostFindings($post);
        $this->assertSame([], TreatmentDocument::validate($doc));

        $json = TreatmentDocument::encodeForStorage($doc);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(2, $decoded['schema_version']);
        $this->assertArrayHasKey('findings', $decoded);

        $ui = TreatmentDocument::findingsForUi(TreatmentDocument::fromStorage($json));
        $this->assertCount(1, $ui);
        $this->assertSame('Chin', $ui[0]['finding_location']);
        $this->assertSame('chin', $ui[0]['location']['id']);
        $this->assertIsArray($ui[0]['dx_desc']);
    }

    public function testValidateRejectsNonArrayFindings(): void
    {
        $errors = TreatmentDocument::validate(['schema_version' => 2, 'findings' => 'nope']);
        $this->assertNotEmpty($errors);
    }

    public function testEmptyStorageYieldsEmptyDocument(): void
    {
        $doc = TreatmentDocument::fromStorage(null);
        $this->assertSame(2, $doc['schema_version']);
        $this->assertSame([], $doc['findings']);
        $this->assertSame([], TreatmentDocument::findingsForUi($doc));
    }

    public function testFromPostAcceptsLocationJsonWithMarkers(): void
    {
        $doc = TreatmentDocument::fromPostFindings([
            [
                'finding_location' => 'Chin',
                'location_json' => json_encode([
                    'id' => 'chin',
                    'label' => 'Chin',
                    'laterality' => 'M',
                    'view' => 'face',
                    'markers' => [
                        ['x' => 0.5123456, 'y' => 0.25],
                        ['x' => 1.5, 'y' => 0.2], // out of range, dropped
                    ],
                ], JSON_THROW_ON_ERROR),
                'dx_s' => 'L82.0',
            ],
        ]);

        $location = $doc['findings'][0]['location'];
        $this->assertSame('chin', $location['id']);
        $this->assertSame('face', $location['view']);
        $this->assertCount(1, $location['markers']);
        $this->assertSame(0.512346, $location['markers'][0]['x']);
        $this->assertArrayNotHasKey('location_json', $doc['findings'][0]);

        $encoded = TreatmentDocument::encodeForStorage($doc);
        $roundTrip = TreatmentDocument::fromStorage($encoded);
        $this->assertSame(
            $location['markers'],
            $roundTrip['findings'][0]['location']['markers']
        );
    }

    public function testFromPostAcceptsMarkersJsonStringInsideLocation(): void
    {
        $doc = TreatmentDocument::fromPostFindings([
            [
                'finding_location' => 'Glabella',
                'location' => [
                    'id' => 'glabella',
                    'label' => 'Glabella',
                    'laterality' => '',
                    'view' => 'face',
                    'markers' => json_encode([['x' => 0.33, 'y' => 0.44]], JSON_THROW_ON_ERROR),
                ],
            ],
        ]);

        $this->assertSame(
            [['x' => 0.33, 'y' => 0.44]],
            $doc['findings'][0]['location']['markers']
        );
    }
}
