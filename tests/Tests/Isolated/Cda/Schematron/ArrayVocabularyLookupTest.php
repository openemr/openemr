<?php

/**
 * ArrayVocabularyLookup isolated test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Schematron;

use OpenEMR\Services\Cda\Schematron\ArrayVocabularyLookup;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ArrayVocabularyLookupTest extends TestCase
{
    public function testReturnsValuesForKnownOid(): void
    {
        $l = new ArrayVocabularyLookup(['1.2.3' => ['a', 'b']]);
        self::assertSame(['a', 'b'], $l->getValuesForOid('1.2.3'));
    }

    public function testReturnsNullForUnknownOid(): void
    {
        $l = new ArrayVocabularyLookup(['1.2.3' => ['a']]);
        self::assertNull($l->getValuesForOid('4.5.6'));
    }

    public function testFromFileLoadsPhpArray(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'vocab');
        try {
            file_put_contents($tmp, "<?php return ['1.2.3' => ['x']];\n");
            $l = ArrayVocabularyLookup::fromFile($tmp);
            self::assertSame(['x'], $l->getValuesForOid('1.2.3'));
        } finally {
            unlink($tmp);
        }
    }

    public function testFromFileRejectsNonArrayReturn(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'vocab');
        try {
            file_put_contents($tmp, "<?php return 'not-an-array';\n");
            $this->expectException(RuntimeException::class);
            ArrayVocabularyLookup::fromFile($tmp);
        } finally {
            unlink($tmp);
        }
    }
}
