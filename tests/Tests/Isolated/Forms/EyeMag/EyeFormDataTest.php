<?php

/**
 * Isolated tests for Eye Form boundary normalization, zone field maps, and value objects.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use OpenEMR\Forms\EyeMag\DrawingBackgroundOption;
use OpenEMR\Forms\EyeMag\EyeFormData;
use OpenEMR\Forms\EyeMag\PatientHandout;
use OpenEMR\Forms\EyeMag\PatientMaterials;
use OpenEMR\Forms\EyeMag\PatientReportDocument;
use OpenEMR\Forms\EyeMag\SavedOverlayMetadata;
use OpenEMR\Forms\EyeMag\Zone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EyeFormDataTest extends TestCase
{
    #[Test]
    public function textNormalizesScalarsAndDiscardsCompoundValues(): void
    {
        $this->assertSame('20/20', EyeFormData::text('20/20'));
        $this->assertSame('42', EyeFormData::text(42));
        $this->assertSame('-1.25', EyeFormData::text(-1.25));
        $this->assertSame('1', EyeFormData::text(true));
        $this->assertSame('', EyeFormData::text(false));
        $this->assertSame('', EyeFormData::text(null));
        $this->assertSame('', EyeFormData::text(['nested']));
        $this->assertSame('', EyeFormData::text(new stdClass()));
    }

    #[Test]
    public function integerParsesNumericScalarsAndDefaultsOtherValuesToZero(): void
    {
        $this->assertSame(15, EyeFormData::integer(15));
        $this->assertSame(-3, EyeFormData::integer('-3'));
        $this->assertSame(18, EyeFormData::integer('18.9'));
        $this->assertSame(0, EyeFormData::integer('not-a-number'));
        $this->assertSame(0, EyeFormData::integer(true));
        $this->assertSame(0, EyeFormData::integer(null));
        $this->assertSame(0, EyeFormData::integer(['12']));
    }

    #[Test]
    public function identifierAcceptsOnlyPositiveBoundedDecimalIdentifiers(): void
    {
        $this->assertSame(1, EyeFormData::identifier(1));
        $this->assertSame(987, EyeFormData::identifier('000987'));
        $this->assertSame(PHP_INT_MAX, EyeFormData::identifier((string) PHP_INT_MAX));
        $this->assertSame(0, EyeFormData::identifier(0));
        $this->assertSame(0, EyeFormData::identifier(-5));
        $this->assertSame(0, EyeFormData::identifier('0'));
        $this->assertSame(0, EyeFormData::identifier('0000'));
        $this->assertSame(0, EyeFormData::identifier('-12'));
        $this->assertSame(0, EyeFormData::identifier('12.5'));
        $this->assertSame(0, EyeFormData::identifier(' 12 '));
        $this->assertSame(0, EyeFormData::identifier('12abc'));
        $this->assertSame(0, EyeFormData::identifier(str_repeat('9', strlen((string) PHP_INT_MAX) + 1)));
        $overflowSameLength = substr((string) PHP_INT_MAX, 0, -1) . '9';
        if ($overflowSameLength !== (string) PHP_INT_MAX) {
            $this->assertSame(0, EyeFormData::identifier($overflowSameLength));
        }
        $this->assertSame(0, EyeFormData::identifier(null));
        $this->assertSame(0, EyeFormData::identifier([7]));
    }

    #[Test]
    public function numberParsesNumericScalarsToFloats(): void
    {
        $this->assertSame(-2.75, EyeFormData::number('-2.75'));
        $this->assertSame(20.0, EyeFormData::number(20));
        $this->assertSame(0.0, EyeFormData::number('CF 3ft'));
        $this->assertSame(0.0, EyeFormData::number(false));
        $this->assertSame(0.0, EyeFormData::number([2.5]));
    }

    #[Test]
    public function rowHelpersKeepStringKeysAndStripCompoundCells(): void
    {
        $this->assertSame([], EyeFormData::row(false));
        $this->assertSame([], EyeFormData::row('invalid'));

        $raw = [
            0 => 'numeric-index-ignored',
            'ODVA' => '20/25',
            'IOP' => 16,
            'NULLABLE' => null,
            'NESTED' => ['unexpected'],
            'OBJECT' => new stdClass(),
        ];

        $row = EyeFormData::row($raw);
        $this->assertArrayNotHasKey(0, $row);
        $this->assertSame('20/25', $row['ODVA']);
        $this->assertArrayHasKey('NESTED', $row);

        $scalarRow = EyeFormData::scalarRow($raw);
        $this->assertSame(
            ['ODVA' => '20/25', 'IOP' => 16, 'NULLABLE' => null],
            $scalarRow
        );

        $textRow = EyeFormData::textRow($row);
        $this->assertSame(
            [
                'ODVA' => '20/25',
                'IOP' => '16',
                'NULLABLE' => '',
                'NESTED' => '',
                'OBJECT' => '',
            ],
            $textRow
        );
    }

    #[Test]
    public function zonesExposeExamFieldsIncludingUnableFlagAndObliqueOveractions(): void
    {
        $this->assertContains('EXT_COMMENTS', Zone::EXT->fields());
        $this->assertContains('ANTSEG_COMMENTS', Zone::ANTSEG->fields());
        $this->assertContains('RETINA_COMMENTS', Zone::RETINA->fields());

        $neuroFields = Zone::NEURO->fields();
        $this->assertContains('VF_UNABLE', $neuroFields);
        foreach (
            [
                'MOTILITY_RRSO_OVER',
                'MOTILITY_RLSO_OVER',
                'MOTILITY_RRIO_OVER',
                'MOTILITY_RLIO_OVER',
                'MOTILITY_LRSO_OVER',
                'MOTILITY_LLSO_OVER',
                'MOTILITY_LRIO_OVER',
                'MOTILITY_LLIO_OVER',
            ] as $overactionField
        ) {
            $this->assertContains($overactionField, $neuroFields);
        }
    }

    #[Test]
    public function drawingBackgroundOptionOmitsFalseFlagsWhenSerialized(): void
    {
        $stock = new DrawingBackgroundOption('default', 'Default drawing', '/img/base.jpg', true);
        $this->assertSame(
            [
                'key' => 'default',
                'label' => 'Default drawing',
                'url' => '/img/base.jpg',
                'structured' => true,
            ],
            $stock->jsonSerialize()
        );

        $archivedSaved = new DrawingBackgroundOption(
            'saved-0123456789abcdef',
            'Retinal lattice',
            '/img/saved.png',
            false,
            true,
            true
        );
        $this->assertSame(
            [
                'key' => 'saved-0123456789abcdef',
                'label' => 'Retinal lattice',
                'url' => '/img/saved.png',
                'structured' => false,
                'saved' => true,
                'archived' => true,
            ],
            $archivedSaved->jsonSerialize()
        );
    }

    #[Test]
    public function savedOverlayMetadataPreservesIdentityAcrossRenameAndArchive(): void
    {
        $sha = str_repeat('a', 64);
        $initial = new SavedOverlayMetadata('saved-fedcba9876543210', 'Initial label', $sha);
        $this->assertFalse($initial->deleted);
        $this->assertSame(
            [
                'version' => 1,
                'key' => 'saved-fedcba9876543210',
                'label' => 'Initial label',
                'sha256' => $sha,
            ],
            $initial->jsonSerialize()
        );

        $renamed = $initial->withLabel('Updated label');
        $this->assertSame('saved-fedcba9876543210', $renamed->key);
        $this->assertSame('Updated label', $renamed->label);
        $this->assertSame($sha, $renamed->sha256);
        $this->assertFalse($renamed->deleted);

        $archived = $renamed->archived();
        $this->assertTrue($archived->deleted);
        $this->assertSame(
            [
                'version' => 1,
                'key' => 'saved-fedcba9876543210',
                'label' => 'Updated label',
                'sha256' => $sha,
                'deleted' => true,
            ],
            $archived->jsonSerialize()
        );
    }

    #[Test]
    public function patientMaterialsValueObjectsRetainHandoutAndDocumentSnapshots(): void
    {
        $handout = new PatientHandout(12, 'Cataract post-op.pdf', '%PDF-1.4 handout');
        $document = new PatientReportDocument(34, 'OCT Macula.png', 'image/png', 'png-bytes');
        $materials = new PatientMaterials('Use drops four times daily.', true, [$handout], [$document]);

        $this->assertSame(12, $materials->handouts[0]->documentId);
        $this->assertSame('Cataract post-op.pdf', $materials->handouts[0]->name);
        $this->assertSame('%PDF-1.4 handout', $materials->handouts[0]->pdfData);
        $this->assertSame(34, $materials->documents[0]->documentId);
        $this->assertSame('OCT Macula.png', $materials->documents[0]->name);
        $this->assertSame('image/png', $materials->documents[0]->mimeType);
        $this->assertSame('png-bytes', $materials->documents[0]->data);
        $this->assertTrue($materials->includeText);
        $this->assertSame('Use drops four times daily.', $materials->content);
    }
}
