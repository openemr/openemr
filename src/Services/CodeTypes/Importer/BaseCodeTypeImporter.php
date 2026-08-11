<?php

/**
 * BaseCodeTypeImporter imports a delimited code set file into the codes table.
 *
 * Subclasses declare which code type they handle via getCodeType() and, where the file layout
 * differs from the default "code, description, short description" ordering, override
 * getMappingForFile() and/or getDelimiter(). Formats that are not delimited at all should
 * override import() outright -- see RXCUIImportService.
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

use Generator;
use InvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\CodeTypes\CodeImportException;
use Throwable;

abstract class BaseCodeTypeImporter
{
    public const DEFAULT_DELIMITER = ',';
    public const DEFAULT_ENCLOSURE = '"';
    public const DEFAULT_ESCAPE = '\\';

    /** Number of records written per transaction. See writeBatch(). */
    private const COMMIT_EVERY = 1000;

    abstract public function getCodeType(): string;

    /**
     * @return array{inserted: int, updated: int}
     *
     * @throws CodeImportException when the code type is unknown or the file cannot be read.
     */
    public function import(string $filePath, bool $isReplace): array
    {
        try {
            return $this->runImport($filePath, $isReplace);
        } catch (CodeImportException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            // Everything below this point -- QueryUtils, the filesystem, a subclass parser --
            // reports failure in its own currency. Normalising it here is what lets callers catch
            // one narrow type instead of \Throwable, which ForbiddenCatchTypeRule forbids.
            throw new CodeImportException('Failed to import code set: ' . $this->getCodeType(), previous: $exception);
        }
    }

    /**
     * @return array{inserted: int, updated: int}
     */
    private function runImport(string $filePath, bool $isReplace): array
    {
        $codeTypeId = $this->resolveCodeTypeId($this->getCodeType());

        // readRecords() is a generator, so nothing is opened or parsed until it is first advanced.
        // Prime it before the DELETE below: an unreadable file or an unrecognised layout must abort
        // the import while the existing code set is still intact.
        $records = $this->readRecords($filePath);
        $records->rewind();

        if ($isReplace) {
            QueryUtils::sqlStatementThrowException("DELETE FROM codes WHERE code_type = ?", [$codeTypeId]);
        }

        $inserted = 0;
        $updated = 0;
        $batch = [];

        foreach ($records as $record) {
            $batch[] = $record;
            if (count($batch) < self::COMMIT_EVERY) {
                continue;
            }
            $this->writeBatch($codeTypeId, $batch, $isReplace, $inserted, $updated);
            $batch = [];
        }
        if ($batch !== []) {
            $this->writeBatch($codeTypeId, $batch, $isReplace, $inserted, $updated);
        }

        return ['inserted' => $inserted, 'updated' => $updated];
    }

    /**
     * Write one batch of records inside a single transaction. Batching keeps InnoDB fast without
     * holding one transaction open for an entire multi-hundred-megabyte file.
     *
     * @param list<array{code: string, code_text: string, code_text_short: string}> $records
     */
    private function writeBatch(
        int $codeTypeId,
        array $records,
        bool $isReplace,
        int &$inserted,
        int &$updated
    ): void {
        QueryUtils::inTransaction(
            function () use ($codeTypeId, $records, $isReplace, &$inserted, &$updated): void {
                foreach ($records as $record) {
                    if (!$isReplace) {
                        $existingId = QueryUtils::fetchSingleValue(
                            "SELECT id FROM codes WHERE code_type = ? AND code = ? LIMIT 1",
                            'id',
                            [$codeTypeId, $record['code']]
                        );
                        if ($existingId !== null) {
                            QueryUtils::sqlStatementThrowException(
                                "UPDATE codes SET code_text = ?, code_text_short = ? "
                                . "WHERE code_type = ? AND code = ?",
                                [$record['code_text'], $record['code_text_short'], $codeTypeId, $record['code']],
                                true
                            );
                            $updated++;
                            continue;
                        }
                    }

                    QueryUtils::sqlInsert(
                        "INSERT INTO codes SET code_type = ?, code = ?, code_text = ?, code_text_short = ?, "
                        . "fee = 0, units = 0",
                        [$codeTypeId, $record['code'], $record['code_text'], $record['code_text_short']]
                    );
                    $inserted++;
                }
            }
        );
    }

    /**
     * Parse the file into code records. Split out from import() so the parsing half of an importer
     * can be exercised without a database.
     *
     * @return Generator<int, array{code: string, code_text: string, code_text_short: string}>
     *
     * @throws CodeImportException when the file cannot be opened or its layout is not recognised.
     */
    public function readRecords(string $filePath): Generator
    {
        $file = $this->openFile($filePath);
        try {
            $mapping = $this->getMappingForFile($file);
            while (($line = $this->readFromFile($file)) !== false) {
                $code = $this->columnValue($line, $mapping['code']);
                // A row with no code is not importable -- this also skips the blank trailing line
                // fgetcsv() reports as [null] at the end of most files.
                if ($code === '') {
                    continue;
                }
                yield [
                    'code' => $code,
                    'code_text' => $this->columnValue($line, $mapping['code_text']),
                    'code_text_short' => $this->columnValue($line, $mapping['code_text_short']),
                ];
            }
        } finally {
            $this->closeFile($file);
        }
    }

    /**
     * Column positions of the three codes columns within a row of the file being imported.
     *
     * @param resource $file Positioned at the start of the file; implementations that read a
     *                       header row to derive the mapping leave the handle past that row.
     *
     * @return array{code: int, code_text: int, code_text_short: int}
     */
    public function getMappingForFile(mixed $file): array
    {
        if (!is_resource($file)) {
            throw new InvalidArgumentException("Invalid file resource provided.");
        }
        return [
            'code' => 0,
            'code_text' => 1,
            'code_text_short' => 2,
        ];
    }

    /**
     * @return resource
     *
     * @throws CodeImportException when the file cannot be opened.
     */
    public function openFile(string $filePath): mixed
    {
        // Checked up front so a missing upload surfaces as an exception rather than an fopen()
        // warning followed by an exception.
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new CodeImportException("File does not exist or is not readable: " . $filePath);
        }
        $file = fopen($filePath, 'r');
        if ($file === false) {
            throw new CodeImportException("Failed to open file: " . $filePath);
        }
        return $file;
    }

    /**
     * @param resource $file
     */
    public function closeFile(mixed $file): bool
    {
        if (!is_resource($file)) {
            throw new InvalidArgumentException("Invalid file resource provided.");
        }
        return fclose($file);
    }

    /**
     * @param resource $file
     *
     * @return list<string|null>|false
     */
    public function readFromFile(mixed $file): array|false
    {
        if (!is_resource($file)) {
            throw new InvalidArgumentException("Invalid file resource provided.");
        }
        // $escape is passed explicitly because PHP 8.4 deprecates relying on its default, which is
        // slated to change. Pinning it to the current default keeps parsing identical and matches
        // league/csv, which the other CSV reader in this codebase (HolidayCsvParser) uses.
        return fgetcsv($file, null, $this->getDelimiter(), self::DEFAULT_ENCLOSURE, self::DEFAULT_ESCAPE);
    }

    public function getDelimiter(): string
    {
        return self::DEFAULT_DELIMITER;
    }

    /**
     * Resolve a ct_key to its ct_id. Replaces the legacy `global $code_types` lookup; the filter on
     * ct_active matches how custom/code_types.inc.php builds that array.
     *
     * @throws CodeImportException when the code type is not defined or not active.
     */
    protected function resolveCodeTypeId(string $codeTypeKey): int
    {
        $codeTypeId = QueryUtils::fetchSingleValue(
            "SELECT ct_id FROM code_types WHERE ct_key = ? AND ct_active = 1",
            'ct_id',
            [$codeTypeKey]
        );
        if (!is_numeric($codeTypeId)) {
            throw new CodeImportException("Code type is not defined or is inactive: " . $codeTypeKey);
        }
        return (int)$codeTypeId;
    }

    /**
     * @param list<string|null> $line
     */
    private function columnValue(array $line, int $position): string
    {
        return $line[$position] ?? '';
    }
}
