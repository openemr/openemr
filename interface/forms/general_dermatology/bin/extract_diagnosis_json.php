<?php
/**
 * One-time extraction script.
 * Run: php bin/extract_diagnosis_json.php
 *
 * Exports FavoriteDiagnosisCodes data to JSON config files.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/App/FavoriteDiagnosisCodes.php';

use GeneralDermatology\App\FavoriteDiagnosisCodes;

$configDir = __DIR__ . '/../config';
if (!is_dir($configDir) && !mkdir($configDir, 0750, true)) {
    fwrite(STDERR, "Failed to create config directory: $configDir\n");
    exit(1);
}

$fdc = new FavoriteDiagnosisCodes();

$codes = $fdc->codeArray();
file_put_contents(
    $configDir . '/diagnosis_codes.json',
    json_encode($codes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);
echo "Written: config/diagnosis_codes.json (" . count($codes) . " entries)\n";

$descriptions = FavoriteDiagnosisCodes::diagnosisAndDescription();
file_put_contents(
    $configDir . '/diagnosis_descriptions.json',
    json_encode($descriptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);
echo "Written: config/diagnosis_descriptions.json (" . count($descriptions) . " entries)\n";
