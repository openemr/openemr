<?php

/**
 * LOINCImportService imports the LOINC code set from the CSV table distributed by Regenstrief.
 *
 * The column order of that file is not stable between releases, so the mapping is derived from the
 * header row rather than hard coded.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2026 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\CodeTypes\Importer;

use OpenEMR\Services\CodeTypes\CodeImportException;

class LOINCImportService extends BaseCodeTypeImporter
{
    public const CODE_TYPE_NAME = 'LOINC';

    public const COLUMN_CODE = 'LOINC_NUM';
    public const COLUMN_CODE_TEXT = 'LONG_COMMON_NAME';
    public const COLUMN_CODE_TEXT_SHORT = 'SHORTNAME';

    public function getCodeType(): string
    {
        return self::CODE_TYPE_NAME;
    }

    /**
     * Consumes the header row and derives the column mapping from it.
     *
     * @param resource $file
     *
     * @return array{code: int, code_text: int, code_text_short: int}
     */
    public function getMappingForFile(mixed $file): array
    {
        if (!is_resource($file)) {
            throw new CodeImportException("Invalid file resource provided");
        }

        $header = $this->readFromFile($file);
        if ($header === false) {
            throw new CodeImportException("LOINC file is empty, no header row was found");
        }
        return $this->getMapping($header);
    }

    /**
     * @param list<string|null> $header
     *
     * @return array{code: int, code_text: int, code_text_short: int}
     *
     * @throws CodeImportException when a required column is absent from the header.
     */
    protected function getMapping(array $header): array
    {
        $mapping = [
            'code' => -1,
            'code_text' => -1,
            'code_text_short' => -1,
        ];
        foreach ($header as $position => $value) {
            // Regenstrief ships Loinc.csv with a UTF-8 BOM, which lands inside the first header
            // cell -- normally LOINC_NUM -- and would otherwise defeat an exact comparison.
            switch ($this->normalizeHeaderCell($value)) {
                case self::COLUMN_CODE:
                    $mapping['code'] = $position;
                    break;
                case self::COLUMN_CODE_TEXT:
                    $mapping['code_text'] = $position;
                    break;
                case self::COLUMN_CODE_TEXT_SHORT:
                    $mapping['code_text_short'] = $position;
                    break;
            }
        }
        if (min($mapping['code'], $mapping['code_text'], $mapping['code_text_short']) < 0) {
            throw new CodeImportException("LOINC is missing required columns, check file format");
        }
        return $mapping;
    }

    private function normalizeHeaderCell(?string $value): string
    {
        return strtoupper(trim(ltrim($value ?? '', "\u{FEFF}")));
    }
}
