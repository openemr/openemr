<?php

/**
 * RXCUIImportService imports RxNorm concept identifiers from the RXNCONSO.RRF table published by
 * the National Library of Medicine, either as the raw .RRF file or still inside the release zip.
 *
 * RRF is pipe delimited but is not CSV -- descriptions routinely contain quote characters that a
 * CSV reader would consume -- so this importer supplies its own row reader rather than using the
 * delimited parsing inherited from BaseCodeTypeImporter. The row filters below reproduce the
 * behaviour of the pre-8.1 interface/super/load_codes.php exactly.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2026 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\CodeTypes\Importer;

use Generator;
use OpenEMR\Services\CodeTypes\CodeImportException;
use ZipArchive;

class RXCUIImportService extends BaseCodeTypeImporter
{
    public const CODE_TYPE_NAME = 'RXCUI';

    /** Name of the concept-names table within the RxNorm release. */
    public const CONSO_FILE_NAME = 'RXNCONSO.RRF';

    private const RRF_DELIMITER = '|';

    /** Column positions within RXNCONSO.RRF. */
    private const COLUMN_RXCUI = 0;
    private const COLUMN_SAB = 11;
    private const COLUMN_STR = 14;
    private const COLUMN_CVF = 17;

    private const MINIMUM_COLUMNS = 18;

    /** Only rows from the RxNorm vocabulary itself are imported. */
    private const SOURCE_ABBREVIATION = 'RXNORM';

    /** Content view flag marking the "Current Prescribable Content" subset. */
    private const CONTENT_VIEW_FLAG = '4096';

    public function getCodeType(): string
    {
        return self::CODE_TYPE_NAME;
    }

    /**
     * @return Generator<int, array{code: string, code_text: string, code_text_short: string}>
     *
     * @throws CodeImportException when RXNCONSO.RRF cannot be located in the uploaded file.
     */
    public function readRecords(string $filePath): Generator
    {
        $zip = new ZipArchive();
        $isZip = $zip->open($filePath) === true;
        $stream = null;

        try {
            if ($isZip) {
                $stream = $this->openConsoEntry($zip);
                if (!is_resource($stream)) {
                    throw new CodeImportException(
                        "Unable to locate " . self::CONSO_FILE_NAME . " in the uploaded archive: " . $filePath
                    );
                }
            } else {
                $stream = $this->openFile($filePath);
            }

            $seenCodes = [];
            while (($line = fgets($stream)) !== false) {
                $columns = explode(self::RRF_DELIMITER, $line);
                if (count($columns) < self::MINIMUM_COLUMNS) {
                    continue;
                }
                if (trim($columns[self::COLUMN_CVF]) !== self::CONTENT_VIEW_FLAG) {
                    continue;
                }
                if (trim($columns[self::COLUMN_SAB]) !== self::SOURCE_ABBREVIATION) {
                    continue;
                }

                $code = $columns[self::COLUMN_RXCUI];
                if ($code === '' || isset($seenCodes[$code])) {
                    continue;
                }
                $seenCodes[$code] = true;

                yield [
                    'code' => $code,
                    'code_text' => $columns[self::COLUMN_STR],
                    'code_text_short' => '',
                ];
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            if ($isZip) {
                $zip->close();
            }
        }
    }

    /**
     * @return resource|false
     */
    private function openConsoEntry(ZipArchive $zip): mixed
    {
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $entryName = $zip->getNameIndex($i);
            if ($entryName !== false && basename($entryName) === self::CONSO_FILE_NAME) {
                return $zip->getStream($entryName);
            }
        }
        return false;
    }
}
