<?php

/**
 * Isolated tests for eye exam copy-forward field selection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use OpenEMR\Forms\EyeMag\CopyForward;
use OpenEMR\Forms\EyeMag\Zone;
use PHPUnit\Framework\TestCase;

class CopyForwardTest extends TestCase
{
    public function testPickReturnsOnlyTheRequestedFields(): void
    {
        $picked = CopyForward::pick(
            ['ODCUP' => '0.3', 'OSCUP' => '0.4', 'PUPIL_COMMENTS' => 'unrelated'],
            ['ODCUP', 'OSCUP'],
        );

        $this->assertSame(['ODCUP' => '0.3', 'OSCUP' => '0.4'], $picked);
    }

    public function testPickDefaultsFieldsMissingFromTheRecordToNull(): void
    {
        $picked = CopyForward::pick(['ODCUP' => '0.3'], ['ODCUP', 'OSCUP']);

        $this->assertSame(['ODCUP' => '0.3', 'OSCUP' => null], $picked);
    }

    public function testPickPreservesFalsyValuesRatherThanDroppingThem(): void
    {
        $picked = CopyForward::pick(['ODCUP' => '0', 'OSCUP' => ''], ['ODCUP', 'OSCUP']);

        $this->assertSame(['ODCUP' => '0', 'OSCUP' => ''], $picked);
    }

    public function testEveryZoneDeclaresFieldsAndNoneRepeatsOne(): void
    {
        foreach (Zone::cases() as $zone) {
            $fields = $zone->fields();

            $this->assertNotEmpty($fields, "{$zone->value} declares no fields");
            $this->assertSame(
                array_values(array_unique($fields)),
                $fields,
                "{$zone->value} repeats a field",
            );
        }
    }

    public function testAllFieldsCoversEveryZone(): void
    {
        $all = CopyForward::allFields();

        foreach (Zone::cases() as $zone) {
            $this->assertSame(
                [],
                array_diff($zone->fields(), $all),
                "whole-form copy omits fields of {$zone->value}",
            );
        }
    }

    public function testAllFieldsCarriesTheImpressionAndDeduplicatesSharedZoneFields(): void
    {
        $all = CopyForward::allFields();

        $this->assertContains('IMP', $all);
        // The tear film measurements belong to both the external and anterior
        // segment exams, so the union has to collapse them.
        $this->assertContains('ODTBUT', Zone::EXT->fields());
        $this->assertContains('ODTBUT', Zone::ANTSEG->fields());
        $this->assertSame(array_values(array_unique($all)), $all);
    }

    /**
     * Drawings and photos live in the documents table, not in a `form_eye_*`
     * column, so naming them here would only ever copy nulls forward.
     */
    public function testNoZoneCarriesDrawingsOrPhotos(): void
    {
        foreach (Zone::cases() as $zone) {
            foreach (['ODPIC', 'OSPIC', 'ODDRAWING', 'OSDRAWING'] as $absent) {
                $this->assertNotContains($absent, $zone->fields(), "{$zone->value} carries {$absent}");
            }
        }
    }

    public function testRecordQueryBindsThePatientAndFormRatherThanInterpolatingThem(): void
    {
        $this->assertStringContainsString('forms.pid = ?', CopyForward::RECORD_QUERY);
        $this->assertStringContainsString('forms.form_id = ?', CopyForward::RECORD_QUERY);
        $this->assertStringNotContainsString('$', CopyForward::RECORD_QUERY);
    }
}
