<?php

/**
 * SchemaRegistry - resolves schematron-schema type ('ccda' | 'qrda1' | 'qrda3')
 * to its committed .sch and vocab.php paths.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

use InvalidArgumentException;

final readonly class SchemaRegistry
{
    public const TYPE_CCDA = 'ccda';
    public const TYPE_QRDA1 = 'qrda1';
    public const TYPE_QRDA3 = 'qrda3';

    private const SCHEMAS = [
        self::TYPE_CCDA => ['dir' => 'ccda', 'sch' => 'Consolidation.sch'],
        self::TYPE_QRDA1 => ['dir' => 'qrda1', 'sch' => '2022_CMS_QRDA_I.sch'],
        self::TYPE_QRDA3 => ['dir' => 'qrda3', 'sch' => '2022_CMS_QRDA_Category_III.sch'],
    ];

    public function __construct(private string $baseDir = __DIR__ . '/schemas')
    {
    }

    public function schematronPath(string $type): string
    {
        return $this->requireEntry($type, 'sch');
    }

    public function vocabPath(string $type): string
    {
        return $this->schemaDir($type) . '/vocab.php';
    }

    public function loadValidator(string $type, bool $includeWarnings = false): SchematronValidator
    {
        return new SchematronValidator(
            ArrayVocabularyLookup::fromFile($this->vocabPath($type)),
            includeWarnings: $includeWarnings,
        );
    }

    private function schemaDir(string $type): string
    {
        if (!isset(self::SCHEMAS[$type])) {
            throw new InvalidArgumentException("Unknown schematron type: $type");
        }
        return $this->baseDir . '/' . self::SCHEMAS[$type]['dir'];
    }

    private function requireEntry(string $type, string $key): string
    {
        if (!isset(self::SCHEMAS[$type])) {
            throw new InvalidArgumentException("Unknown schematron type: $type");
        }
        return $this->schemaDir($type) . '/' . self::SCHEMAS[$type][$key];
    }
}
