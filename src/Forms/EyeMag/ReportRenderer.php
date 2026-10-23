<?php

/**
 * Eye Form report sections and PDF drawing layouts.
 *
 * @package OpenEMR
 * @author Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@MedFetch.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

use Mpdf\Mpdf;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class ReportRenderer
{

    /**
     * @param list<array{label:string,od:array<string,string>,os:array<string,string>,notes:string}> $sections
     * @param array<string,string> $od
     * @param array<string,string> $os
     */
    private static function addRefractionSection(array &$sections, string $label, array $od, array $os, string $notes = ''): void
    {
        if (!self::hasRefractionValues($od) && !self::hasRefractionValues($os) && trim($notes) === '') {
            return;
        }
        $sections[] = ['label' => $label, 'od' => $od, 'os' => $os, 'notes' => trim($notes)];
    }

    /** @param array<string,string> $row */
    private static function hasRefractionValues(array $row): bool
    {
        foreach ($row as $key => $value) {
            if ($key !== 'eye' && trim($value) !== '') {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<string,string> $values
     * @return array<string,string>
     */
    private static function refractionRow(string $eye, array $values): array
    {
        return array_merge([
            'eye' => $eye, 'sphere' => '', 'cylinder' => '', 'axis' => '',
            'prism' => '', 'base_curve' => '', 'diameter' => '', 'distance' => '',
            'mid' => '', 'add' => '', 'near' => '',
        ], $values);
    }

/**
 * Collect only documented refraction sections for reports and encounter previews.
 */
/** @return list<array{label:string,od:array<string,string>,os:array<string,string>,notes:string}> */
    public static function refractions(int $pid, int $encounter, int $formId): array
    {
        $record = QueryUtils::querySingleRow(
        "SELECT r.*, a.* FROM form_eye_refraction r
          JOIN form_eye_acuity a ON a.id=r.id AND a.pid=r.pid
         WHERE r.id=? AND r.pid=?",
        [$formId, $pid]
        );
        $record = EyeFormData::textRow(EyeFormData::row($record));
        $sections = [];
        $wear = QueryUtils::sqlStatementThrowException(
        "SELECT * FROM form_eye_mag_wearing WHERE PID=? AND FORM_ID=? AND ENCOUNTER=? ORDER BY RX_NUMBER",
        [$pid, $formId, $encounter]
        );
        while ($wearing = QueryUtils::fetchArrayFromResultSet($wear)) {
            $wearing = EyeFormData::textRow(EyeFormData::row($wearing));
            $rxNumber = EyeFormData::integer($wearing['RX_NUMBER'] ?? 0);
            $rxTypes = ['0' => '', '1' => 'Bifocals', '2' => 'Trifocals', '3' => 'Progressive'];
            $rxType = $rxTypes[EyeFormData::text($wearing['RX_TYPE'] ?? '0')] ?? EyeFormData::text($wearing['RX_TYPE'] ?? '');
            $odPrism = trim(EyeFormData::text($wearing['ODPRISM'] ?? '')) ?: trim(trim(($wearing['ODHPD'] ?? '') . ' ' . ($wearing['ODHBASE'] ?? '')) . ' ' . trim(($wearing['ODVPD'] ?? '') . ' ' . ($wearing['ODVBASE'] ?? '')));
            $osPrism = trim(EyeFormData::text($wearing['OSPRISM'] ?? '')) ?: trim(trim(($wearing['OSHPD'] ?? '') . ' ' . ($wearing['OSHBASE'] ?? '')) . ' ' . trim(($wearing['OSVPD'] ?? '') . ' ' . ($wearing['OSVBASE'] ?? '')));
            $label = 'Current Rx' . ($rxNumber ? ' #' . $rxNumber : '') . ($rxType !== '' ? ' · ' . $rxType : '');
            self::addRefractionSection($sections,
            $label,
            self::refractionRow('OD', [
                'sphere' => $wearing['ODSPH'] ?? '', 'cylinder' => $wearing['ODCYL'] ?? '',
                'axis' => $wearing['ODAXIS'] ?? '', 'prism' => $odPrism,
                'distance' => $wearing['ODVA'] ?? '', 'mid' => $wearing['ODMIDADD'] ?? '',
                'add' => $wearing['ODADD'] ?? '', 'near' => $wearing['ODNEARVA'] ?? '',
            ]),
            self::refractionRow('OS', [
                'sphere' => $wearing['OSSPH'] ?? '', 'cylinder' => $wearing['OSCYL'] ?? '',
                'axis' => $wearing['OSAXIS'] ?? '', 'prism' => $osPrism,
                'distance' => $wearing['OSVA'] ?? '', 'mid' => $wearing['OSMIDADD'] ?? '',
                'add' => $wearing['OSADD'] ?? '', 'near' => $wearing['OSNEARVA'] ?? '',
            ]),
            $wearing['COMMENTS'] ?? ''
            );
        }

        $definitions = [
        ['Auto Refraction', 'AR', 'ARNEARODVA', 'ARNEAROSVA', ''],
        ['Manifest Refraction', 'MR', 'MRNEARODVA', 'MRNEAROSVA', ''],
        ['Cycloplegic Refraction', 'CR', '', '', 'CRCOMMENTS'],
        ];
        foreach ($definitions as [$label, $prefix, $odNear, $osNear, $notesField]) {
            self::addRefractionSection($sections,
            $label,
            self::refractionRow('OD', [
                'sphere' => $record[$prefix . 'ODSPH'] ?? '', 'cylinder' => $record[$prefix . 'ODCYL'] ?? '',
                'axis' => $record[$prefix . 'ODAXIS'] ?? '', 'prism' => $record[$prefix . 'ODPRISM'] ?? '',
                'distance' => $record[$prefix . 'ODVA'] ?? '', 'add' => $record[$prefix . 'ODADD'] ?? '',
                'near' => $odNear ? ($record[$odNear] ?? '') : '',
            ]),
            self::refractionRow('OS', [
                'sphere' => $record[$prefix . 'OSSPH'] ?? '', 'cylinder' => $record[$prefix . 'OSCYL'] ?? '',
                'axis' => $record[$prefix . 'OSAXIS'] ?? '', 'prism' => $record[$prefix . 'OSPRISM'] ?? '',
                'distance' => $record[$prefix . 'OSVA'] ?? '', 'add' => $record[$prefix . 'OSADD'] ?? '',
                'near' => $osNear ? ($record[$osNear] ?? '') : '',
            ]),
            $notesField ? ($record[$notesField] ?? '') : ''
            );
        }

        self::addRefractionSection($sections,
        'Contact Lens',
        self::refractionRow('OD', [
            'sphere' => $record['CTLODSPH'] ?? '', 'cylinder' => $record['CTLODCYL'] ?? '',
            'axis' => $record['CTLODAXIS'] ?? '',
            'base_curve' => $record['CTLODBC'] ?? '', 'diameter' => $record['CTLODDIAM'] ?? '',
            'distance' => $record['CTLODVA'] ?? '', 'add' => $record['CTLODADD'] ?? '',
        ]),
        self::refractionRow('OS', [
            'sphere' => $record['CTLOSSPH'] ?? '', 'cylinder' => $record['CTLOSCYL'] ?? '',
            'axis' => $record['CTLOSAXIS'] ?? '',
            'base_curve' => $record['CTLOSBC'] ?? '', 'diameter' => $record['CTLOSDIAM'] ?? '',
            'distance' => $record['CTLOSVA'] ?? '', 'add' => $record['CTLOSADD'] ?? '',
        ]),
        $record['CTL_COMMENTS'] ?? ''
        );

        return $sections;
    }

/** @param list<array{label:string,od:array<string,string>,os:array<string,string>,notes:string}> $sections */
    public static function renderRefractionSummary(array $sections): void
    {
        if ($sections === []) {
            return;
        }
        ?>
    <style>
        .eye-rx-report { background:var(--white,#fff); border:1px solid var(--gray-300,#d6dbe0); border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin:10px 0; overflow:hidden; text-align:left; }
        .eye-rx-report__title { background:var(--primary,#5f8b96); color:var(--white,#fff); font-size:1rem; font-weight:600; padding:8px 12px; }
        .eye-rx-report__scroll { overflow-x:auto; }
        .eye-rx-report table { border-collapse:collapse; margin:0; min-width:680px; width:100%; }
        .eye-rx-report th { background:var(--gray-100,#f2f4f6); color:var(--body-color,#212529); font-size:.78rem; padding:6px; text-align:center; }
        .eye-rx-report td { border-top:1px solid var(--gray-200,#e7eaed); color:var(--body-color,#212529); padding:6px; text-align:center; white-space:nowrap; }
        .eye-rx-report td:first-child { background:rgba(95,139,150,.1); font-weight:600; text-align:left; white-space:normal; }
        .eye-rx-report__notes td { background:var(--gray-100,#f7f8f9); font-size:.82rem; text-align:left !important; white-space:normal; }
    </style>
    <section class="eye-rx-report">
        <div class="eye-rx-report__title"><i class="fas fa-glasses"></i> <?php echo xlt('Refraction'); ?></div>
        <div class="eye-rx-report__scroll">
            <table>
                <thead><tr>
                    <th><?php echo xlt('Type'); ?></th><th><?php echo xlt('Eye'); ?></th>
                    <th><?php echo xlt('Sphere'); ?></th><th><?php echo xlt('Cylinder'); ?></th>
                    <th><?php echo xlt('Axis'); ?></th><th><?php echo xlt('Prisms'); ?></th>
                    <th><?php echo xlt('Base Curve'); ?></th><th><?php echo xlt('Diameter'); ?></th>
                    <th><?php echo xlt('Distance VA'); ?></th><th><?php echo xlt('Mid'); ?></th>
                    <th><?php echo xlt('Add'); ?></th><th><?php echo xlt('Near VA'); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ($sections as $section) {
                    foreach (['od', 'os'] as $rowIndex => $eyeKey) {
                        $rxRow = $section[$eyeKey]; ?>
                        <tr>
                            <?php if ($rowIndex === 0) {
                                ?><td rowspan="2"><?php echo text($section['label']); ?></td><?php } ?>
                            <td><b><?php echo text($rxRow['eye']); ?></b></td>
                            <?php foreach (['sphere', 'cylinder', 'axis', 'prism', 'base_curve', 'diameter', 'distance', 'mid', 'add', 'near'] as $field) { ?>
                                <td><?php echo text($rxRow[$field] !== '' ? $rxRow[$field] : '—'); ?></td>
                            <?php } ?>
                        </tr>
                    <?php }
                    if ($section['notes'] !== '') { ?>
                        <tr class="eye-rx-report__notes"><td colspan="12"><b><?php echo xlt('Notes'); ?>:</b> <?php echo text($section['notes']); ?></td></tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </section>
        <?php
    }

/**
 * Acknowledge both kinds of instructions and preserve originals in PDF output.
 * @param callable(int,int):array{content:string,include_text:int,handout_document_id:int|null,handout_document_ids:string|null,handouts:list<array{document_id:int,name:string,pdf_data:string}>} $loadMaterials
 * @param callable(Mpdf,int,int,array{content:string,include_text:int,handout_document_id:int|null,handout_document_ids:string|null,handouts:list<array{document_id:int,name:string,pdf_data:string}>}):bool $embedMaterials
 */
    public static function materialsText(int $pid, int $formId, ?Mpdf $materialsPdf, bool $pdfOutput, callable $loadMaterials, callable $embedMaterials): void
    {
        $materials = $loadMaterials($formId, $pid);
        $content = EyeFormData::text($materials['content']);
        $includeText = EyeFormData::integer($materials['include_text']) === 1 && trim($content) !== '';
        $includeHandout = $materials['handouts'] !== [];
        if (!$includeText && !$includeHandout) {
            return;
        }
        $embedded = false;
        if ($pdfOutput) {
            // Core custom_report.php owns a global mPDF; Taskman passes its local one.
            $embedded = ($materialsPdf instanceof Mpdf && $embedMaterials($materialsPdf, $formId, $pid, $materials));
            echo '<pagebreak />';
        }
        echo '<div class="report_exam_group" style="text-align:left;width:100%;">';
        self::zoneHeading(xl('Patient Materials'), $pdfOutput);
        if ($includeText) {
            echo '<p><b>' . xlt('Patient instructions included') . '</b></p>';
            echo '<div style="white-space:pre-wrap;padding:6px;">' . nl2br(text($content)) . '</div>';
        }
        foreach ($materials['handouts'] as $handout) {
            if ($handout['pdf_data'] === '' || ($pdfOutput && !$embedded)) {
                echo '<p><b>' . xlt('Selected PDF handout could not be included. Restore or replace it and generate the report again.') . '</b></p>';
                echo '<p>' . text($handout['name'] ?: (xl('Document') . ' #' . EyeFormData::text($handout['document_id']))) . '</p>';
            } else {
                echo '<p><b>' . xlt('Patient PDF instructions included') . ':</b> ' . text($handout['name']) . '</p>';
                if ($pdfOutput) {
                    echo '<p>' . xlt('An exact copy of the original PDF is embedded in this report. Use a PDF reader with an Attachments panel to open or print it.') . '</p>';
                }
            }
        }
        echo '</div>';
    }

/**
 * Ignore legacy canvas documents produced by the old mouseout handler when
 * the clinician never marked the base image. JPEG re-encoding can alter edge
 * pixels slightly, so only differences greater than the verified compression
 * noise are treated as clinical marks.
 */
    public static function drawingIsUntouchedBase(string $drawingData, string $zone): bool
    {
        if (!function_exists('imagecreatefromstring')) {
            return false;
        }
        $baseFile = OEGlobalsBag::getInstance()->getProjectDir()
        . '/interface/forms/eye_mag/images/OU_' . $zone . '_BASE.jpg';
        if (!is_file($baseFile)) {
            return false;
        }
        $drawing = @imagecreatefromstring($drawingData);
        $base = @imagecreatefromjpeg($baseFile);
        if (!$drawing || !$base || imagesx($drawing) !== imagesx($base) || imagesy($drawing) !== imagesy($base)) {
            if ($drawing) {
                imagedestroy($drawing);
            }
            if ($base) {
                imagedestroy($base);
            }
            return false;
        }
        $width = imagesx($drawing);
        $height = imagesy($drawing);
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $drawingColor = imagecolorat($drawing, $x, $y);
                $baseColor = imagecolorat($base, $x, $y);
                $difference = abs((($drawingColor >> 16) & 255) - (($baseColor >> 16) & 255))
                + abs((($drawingColor >> 8) & 255) - (($baseColor >> 8) & 255))
                + abs(($drawingColor & 255) - ($baseColor & 255));
                if ($difference > 40) {
                    imagedestroy($drawing);
                    imagedestroy($base);
                    return false;
                }
            }
        }
        imagedestroy($drawing);
        imagedestroy($base);
        return true;
    }

/**
 * End of the PDF body: strip every inline font-size outside the letterhead so
 * the whole report inherits one size from .rpt-body. The markup carries over a
 * hundred inline sizes (px, em, rem), and mPDF lets them beat any stylesheet
 * rule, so they cannot be overridden from CSS alone.
 */
    public static function flushPdf(bool $pdfOutput): void
    {
        if (!$pdfOutput) {
            return;
        }
        $html = EyeFormData::text(ob_get_clean());
        $parts = preg_split('~(<div class="rpt-letterhead">.*?</div><!--/rpt-letterhead-->)~s', $html, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$html];
        foreach ($parts as $i => $part) {
            if (str_starts_with($part, '<div class="rpt-letterhead">')) {
                continue;
            }
            $parts[$i] = preg_replace_callback('~\bstyle\s*=\s*(["\'])(.*?)\1~s', fn($m): string => 'style=' . $m[1] . preg_replace('~font-size\s*:\s*[^;"\']+;?~i', '', $m[2]) . $m[1], $part) ?? $part;
        }
        echo '<div class="rpt-body">' . implode('', $parts) . '</div>';
    }

/**
 * Section heading for an exam zone: a banded heading in the PDF, the
 * underlined label on screen.
 */
    public static function zoneHeading(string $title, bool $pdfOutput): void
    {
        if ($pdfOutput) {
            // A one-cell table: mPDF shrinks a div's background to its text inside a
            // table cell, and carries the white text colour into the next line.
            echo '<table style="width:100%;border-collapse:collapse;margin-bottom:3pt;"><tr><td class="rpt-h">' . text($title) . '</td></tr></table>';
        } else {
            echo '<span style="font-weight:bold;text-decoration: underline">' . text($title) . ':</span>';
        }
    }

/**
 * Right / Left column header of a zone table. In the PDF every zone uses the
 * same fixed column widths, so the finding labels line up across all zones.
 */
    public static function rlHeader(bool $pdfOutput): void
    {
        $w = $pdfOutput ? ['38%', '24%', '38%'] : ['150px', '100px', '150px'];
        echo '<tr class="rl-head">'
        . '<td class="rl-r" style="font-weight:bold;text-align:right;padding-right:10px;text-decoration:underline;width:' . $w[0] . ';">' . xlt('Right') . '</td>'
        . '<td class="rl-m" style="width:' . $w[1] . ';"></td>'
        . '<td class="rl-l" style="font-weight:bold;text-align:left;padding-left:10px;text-decoration:underline;width:' . $w[2] . ';">' . xlt('Left') . '</td>'
        . '</tr>';
    }

/**
 * Output one exam zone (heading + findings table) with its drawing.
 * On screen the zone prints immediately, drawing to its right. For the PDF it
 * is queued for self::pdfZoneLayout(), and the pixel widths written for
 * the screen are dropped so the fixed header widths set the columns.
 */
/**
 * @param list<array{section:string,drawing:string,fullwidth?:bool}> $pdf_zones
 * @param callable(string,mixed,mixed):void $displayDrawing
 */
    public static function zoneEmit(string $section, string $zone, mixed $encounter, mixed $pid, array &$pdf_zones, bool $pdfOutput, callable $displayDrawing): void
    {
        ob_start();
        $displayDrawing($zone, $encounter, $pid);
        $drawing = trim(EyeFormData::text(ob_get_clean()));
        if ($pdfOutput) {
            $section = preg_replace_callback('~<td\b([^>]*)>~', function ($m) {
                if (str_contains($m[1], 'class="rl-')) {
                    return $m[0];
                }
                return '<td' . preg_replace('~\bwidth\s*:\s*[\d.]+px\s*;?~i', '', $m[1]) . '>';
            }, $section) ?? $section;
            $pdf_zones[] = ['section' => $section, 'drawing' => $drawing];
            return;
        }
        echo '<div class="report_exam_group"><table><tr>'
        . '<td style="text-align:left;vertical-align:top;padding:1px;">' . $section . '</td>'
        . '<td style="text-align:center;padding:1px;vertical-align:middle;">' . $drawing . '</td>'
        . '</tr></table></div>';
    }

/**
 * PDF layout of the exam zones, in two half-width columns. A zone with a
 * drawing takes a row, the drawing pinned to its right. A zone without one
 * moves up into the free right half of the row above when there is room.
 */
/** @param list<array{section:string,drawing:string,fullwidth?:bool}> $zones */
    public static function pdfZoneLayout(array $zones): void
    {
        $rows = [];
        foreach ($zones as $z) {
            if ($z['fullwidth'] ?? false) {
                // No left section ever exists for this entry (Scleral
                // Depression): one centered row across both columns.
                $rows[] = [$z['section'] . $z['drawing'], null, 'full'];
                continue;
            }
            if ($z['drawing'] !== '') {
                $rows[] = [$z['section'], $z['drawing'], true];
            } elseif ($rows && end($rows)[1] === null) {
                $rows[array_key_last($rows)][1] = $z['section'];
            } else {
                $rows[] = [$z['section'], null, false];
            }
        }
        foreach ($rows as [$left, $right, $is_drawing]) {
            if ($is_drawing === 'full') {
                echo '<table class="rpt-pdf" style="width:100%;border-collapse:collapse;margin-top:10pt;"><tr>'
                . '<td colspan="2" style="text-align:center;padding:0;">' . $left . '</td>'
                . '</tr></table>';
                continue;
            }
            // The 10pt row gap and the 5mm gutter keep neighbouring zones visually apart.
            echo '<table class="rpt-pdf" style="width:100%;border-collapse:collapse;margin-top:10pt;"><tr>'
            . '<td style="width:50%;vertical-align:top;padding:0 2.5mm 0 0;">' . $left . '</td>'
            . '<td style="width:50%;vertical-align:' . ($is_drawing ? 'middle' : 'top') . ';text-align:' . ($is_drawing ? 'center' : 'left') . ';padding:0 0 0 2.5mm;">' . ($right ?? '') . '</td>'
            . '</tr></table>';
        }
    }

/**
 * Render the practice-selected motility notation without relying on nested
 * tables or background images, which mPDF can clip inside the five-column
 * summary. Both X and H charts show one perpendicular tick for each 25%
 * limitation grade.
 */
/** @param array<string,mixed> $values */
    public static function motilityDiagram(string $display, array $values, bool $pdfOutput): string
    {
        $display = strtoupper($display) === 'H' ? 'H' : 'X';
        $grade = static fn(string $key): int => max(0, min(4, EyeFormData::integer($values[$key] ?? 0)));
        $line = static fn(float $x1, float $y1, float $x2, float $y2, float $width = 1.8): string => sprintf(
        '<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#111" stroke-width="%.2f" stroke-linecap="square"/>',
        $x1,
        $y1,
        $x2,
        $y2,
        $width
        );
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">';

        if ($display === 'H') {
            $svg .= $line(21, 25, 21, 95, 2.5)
            . $line(99, 25, 99, 95, 2.5)
            . $line(21, 60, 99, 60, 2.5);
            $ticks = static function (float $cx, float $cy, float $dx, float $dy, string $key, float $outer) use ($grade, $line): string {
                $output = '';
                for ($index = 0; $index < $grade($key); ++$index) {
                    $distance = $outer - ($index * 6);
                    $tx = $cx + ($dx * $distance);
                    $ty = $cy + ($dy * $distance);
                    $px = -$dy * 5;
                    $py = $dx * 5;
                    $output .= $line($tx - $px, $ty - $py, $tx + $px, $ty + $py, 2);
                }
                return $output;
            };
            $svg .= $ticks(21, 60, 0, -1, 'upperLeft', 30)
            . $ticks(60, 60, -1, 0, 'left', 22)
            . $ticks(21, 60, 0, 1, 'lowerLeft', 30)
            . $ticks(99, 60, 0, -1, 'upperRight', 30)
            . $ticks(60, 60, 1, 0, 'right', 22)
            . $ticks(99, 60, 0, 1, 'lowerRight', 30);
        } else {
            $directions = [
            'up' => [0, -1], 'down' => [0, 1],
            'left' => [-1, 0], 'right' => [1, 0],
            'upperLeft' => [-0.7071, -0.7071],
            'upperRight' => [0.7071, -0.7071],
            'lowerLeft' => [-0.7071, 0.7071],
            'lowerRight' => [0.7071, 0.7071],
            ];
            foreach ($directions as $key => [$dx, $dy]) {
                $svg .= $line(60, 60, 60 + ($dx * 35), 60 + ($dy * 35), 1.6);
                for ($index = 0; $index < $grade($key); ++$index) {
                    $distance = 31 - ($index * 6);
                    $tx = 60 + ($dx * $distance);
                    $ty = 60 + ($dy * $distance);
                    $px = -$dy * 5;
                    $py = $dx * 5;
                    $svg .= $line($tx - $px, $ty - $py, $tx + $px, $ty + $py, 2);
                }
            }
        }
        $svg .= '</svg>';
        $size = $pdfOutput ? '15mm' : '58px';
        return '<img alt="" src="data:image/svg+xml;base64,' . base64_encode($svg)
        . '" style="display:block;margin:0 auto;width:' . $size . ';height:' . $size . ';" />';
    }

/**
 * One Alternate Cover Test grid, drawn with the same separators as the form:
 * a 3x3 tic-tac-toe of gaze positions with the head-tilt cells below.
 */
/**
 * @param array<int,mixed> $v
 * @param callable(mixed):string $formatCell
 */
    public static function actGrid(string $title, array $v, bool $pdfOutput, callable $formatCell): void
    {
        // Every cell, labels included, is the same fixed square, so all grids are
        // the same size whether a gaze position holds data or not. 5 x 13mm fits "ORTHO" at the 9pt body size.
        $side = $pdfOutput ? '13mm' : '58px';
        $size = "width:$side;height:$side;min-width:$side;max-width:$side;";
        $td = fn(string $content, string $style = ''): string => '<td class="act-cell" style="' . $size . 'text-align:center;vertical-align:middle;overflow:hidden;' . $style . '">' . $content . '</td>';
        $cell = fn(int $k, string $border): string => $td($formatCell($v[$k] ?? ''), $border);
        $thin = '1pt solid #000';
        $thick = '2pt solid #000';
        $icon = fn(string $cls): string => $pdfOutput ? self::actArrow(str_contains($cls, 'reply')) : '<i class="fas ' . $cls . '"></i>';
        echo '<table style="border:1pt solid grey;border-collapse:collapse;table-layout:fixed;"><tr><td class="act-tab" colspan="5" style="height:' . ($pdfOutput ? '4.5mm' : '25px') . ';'
        . ($pdfOutput ? '' : 'background-color:#2d6775;color:#fff;padding:2px 6px;') . '">' . text($title) . '</td></tr>';
        echo '<tr>' . $td(xlt('R{{right}}'), 'font-weight:bold;')
        . $cell(1, "border-right:$thin;border-bottom:$thin;")
        . $cell(2, "border-left:$thin;border-right:$thin;border-bottom:$thin;")
        . $cell(3, "border-left:$thin;border-bottom:$thin;")
        . $td(xlt('L{{left}}'), 'font-weight:bold;') . '</tr>';
        echo '<tr>' . $td($icon('fa-reply rotate-left'))
        . $cell(4, "border-top:$thin;border-right:$thin;border-bottom:$thin;")
        . $cell(5, "border:$thin;")
        . $cell(6, "border-top:$thin;border-left:$thin;border-bottom:$thin;")
        . $td($icon('fa-share rotate-right')) . '</tr>';
        echo '<tr>'
        . $cell(10, "border-top:$thick;border-right:$thick;")
        . $cell(7, "border-top:$thin;border-right:$thin;")
        . $cell(8, "border-top:$thin;border-left:$thin;border-right:$thin;")
        . $cell(9, "border-top:$thin;border-left:$thin;")
        . $cell(11, "border-top:$thick;border-left:$thick;")
        . '</tr></table>';
    }

/**
 * mPDF draws neither Font Awesome glyphs nor CSS rotation, so the PDF gets
 * the same curved "down and outward" head-tilt arrows as inline SVG: the tail
 * starts at the grid side and the head points down on the outside.
 */
    public static function actArrow(bool $left_side): string
    {
        $path = $left_side
        ? 'M17 6 C 10 2, 5 5, 5 13'
        : 'M3 6 C 10 2, 15 5, 15 13';
        $head = $left_side ? 'M2 11 L5 16 L8 11' : 'M12 11 L15 16 L18 11';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18">'
        . '<path d="' . $path . '" fill="none" stroke="#000" stroke-width="1.6"/>'
        . '<path d="' . $head . '" fill="none" stroke="#000" stroke-width="1.6"/></svg>';
        return '<img src="data:image/svg+xml;base64,' . base64_encode($svg) . '" style="width:5mm;height:4.5mm;" />';
    }
}
