<?php

/**
 * Regenerate the committed schematron vocab.php files from source .sch + voc.xml.
 *
 * The vocab files live at src/Services/Cda/Schematron/schemas/<type>/vocab.php
 * and are generated from the openemr/oe-schematron-service GitHub release's
 * .sch + voc.xml pair. Run this whenever the schematron IG revision bumps.
 *
 * Usage:
 *   php bin/regen-schematron-vocab.php <path-to-oe-schematron-service-checkout>
 *
 * Where <path> is a local checkout (or extracted tarball) of
 * https://github.com/openemr/oe-schematron-service containing:
 *   schematron/ccda/{Consolidation.sch,voc.xml}
 *   schematron/qrda1/{2022_CMS_QRDA_I.sch,voc.xml}
 *   schematron/qrda3/{2022_CMS_QRDA_Category_III.sch,voc.xml}
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use OpenEMR\Services\Cda\Schematron\VocabularyExtractor;

if ($argc !== 2) {
    fwrite(STDERR, "usage: regen-schematron-vocab.php <oe-schematron-service-root>\n");
    exit(2);
}

$sourceRoot = rtrim($argv[1], '/');
$outputRoot = __DIR__ . '/../src/Services/Cda/Schematron/schemas';

$targets = [
    'ccda' => 'Consolidation.sch',
    'qrda1' => '2022_CMS_QRDA_I.sch',
    'qrda3' => '2022_CMS_QRDA_Category_III.sch',
];

$extractor = new VocabularyExtractor();

foreach ($targets as $type => $schFile) {
    $schPath = "$sourceRoot/schematron/$type/$schFile";
    $vocPath = "$sourceRoot/schematron/$type/voc.xml";
    if (!is_file($schPath) || !is_file($vocPath)) {
        fwrite(STDERR, "  [skip] $type — missing $schPath or $vocPath\n");
        continue;
    }

    $sch = file_get_contents($schPath);
    $voc = file_get_contents($vocPath);
    if ($sch === false || $voc === false) {
        fwrite(STDERR, "  [error] $type — read failed\n");
        exit(1);
    }
    $result = $extractor->extract($sch, $voc);
    $outSchDir = "$outputRoot/$type";
    if (!is_dir($outSchDir)) {
        mkdir($outSchDir, 0755, true);
    }
    file_put_contents("$outSchDir/$schFile", $sch);
    file_put_contents("$outSchDir/vocab.php", $extractor->renderPhpFile($result['resolved'], $schFile, 'voc.xml'));

    printf(
        "  [%s] %d OIDs (%d resolved, %d missing), wrote %s + vocab.php\n",
        $type,
        count($result['oids']),
        count($result['resolved']),
        count($result['missing']),
        $schFile,
    );
    if ($result['missing'] !== []) {
        foreach ($result['missing'] as $oid) {
            fwrite(STDERR, "      missing OID: $oid\n");
        }
    }
}
