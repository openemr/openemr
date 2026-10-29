<?php

/**
 * The patient's shot record as a PDF: the facility's address, the patient, a table of
 * immunizations and a signature line.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Pdf;

final class ShotRecordPdf
{
    /**
     * @param array<mixed>                     $facility the user's facility, from FacilityService::getFacilityForUserFormatted()
     * @param array<mixed>|false               $patient  patient_name, patient_DOB and patient_address; false when no row was found
     * @param array<int, array<string, mixed>> $rows     one row per immunization, keyed by column heading
     * @param array{date_of_birth: string, none_on_file: string, signature: string} $labels already translated
     */
    public static function build(array $facility, array|false $patient, array $rows, string $title, array $labels): \Cezpdf
    {
        $pdf = new \Cezpdf("LETTER");
        $pdf->ezSetMargins(72, 30, 50, 30);
        $pdf->selectFont('Helvetica');

        $pdf->ezText(self::text($facility, 'facility_address'), 0, ['justification' => "center"]);

        $pdf->ezText("\n" . self::text($patient, 'patient_name') . "\n" . $labels['date_of_birth'] . ": " . self::text($patient, 'patient_DOB') . "\n" . self::text($patient, 'patient_address'));
        $pdf->ezText("\n");

        // Cezpdf::ezTable() reads the column names from the first row, so an
        // empty list throws a TypeError and the page fails with a 500.
        if ($rows === []) {
            $pdf->ezText($title, 0, ['justification' => 'center']);
            $pdf->ezText("\n" . $labels['none_on_file']);
        } else {
            $pdf->ezTable($rows, title: $title, options: ['maxWidth' => 550, 'fontSize' => 8]);
        }
        $pdf->ezText("\n\n\n\n" . $labels['signature'] . ":________________________________", 0, ['justification' => 'right']);
        return $pdf;
    }

    /**
     * @param array<mixed>|false $row
     */
    private static function text(array|false $row, string $key): string
    {
        $value = $row === false ? null : ($row[$key] ?? null);
        return is_scalar($value) ? (string) $value : '';
    }
}
