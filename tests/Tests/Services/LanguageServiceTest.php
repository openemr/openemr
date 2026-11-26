<?php

/**
 * LanguageServiceTest.php
 *
 * Characterizes insert_language_log (interface/language/language.inc.php) and verifies its
 * replacement, LanguageService::insertLanguageLog(), behave identically against the lang_custom
 * table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\LanguageService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LanguageServiceTest extends TestCase
{
    // Every value written by this test (lang_description or constant_name) starts with this
    // prefix, so tearDown() can find and remove exactly the rows this test created.
    private const TAG = 'langsvc-test-';

    /**
     * Loads the legacy delegators under test.
     */
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../interface/language/language.inc.php';
    }

    /**
     * Starts from a table without rows left by an earlier run.
     */
    protected function setUp(): void
    {
        $this->deleteFixtureRows();
    }

    /**
     * Removes the rows this test wrote.
     */
    protected function tearDown(): void
    {
        $this->deleteFixtureRows();
    }

    /**
     * Deletes every lang_custom row whose description or constant carries the test prefix.
     */
    private function deleteFixtureRows(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `lang_custom` WHERE `lang_description` LIKE ? OR `constant_name` LIKE ?",
            [self::TAG . '%', self::TAG . '%']
        );
    }

    /**
     * @return list<array<mixed>>
     */
    private function fetchRows(string $langDesc, string $consName): array
    {
        return QueryUtils::fetchRecords(
            "SELECT * FROM `lang_custom`"
            . " WHERE `lang_description` COLLATE utf8mb4_bin = ? AND `constant_name` COLLATE utf8mb4_bin = ?",
            [$langDesc, $consName]
        );
    }

    /**
     * Rows matching a lang_description regardless of constant_name (the NEW LANGUAGE branch
     * leaves constant_name NULL, not '').
     *
     * @return list<array<mixed>>
     */
    private function fetchRowsByDescription(string $langDesc): array
    {
        return QueryUtils::fetchRecords(
            "SELECT * FROM `lang_custom` WHERE `lang_description` COLLATE utf8mb4_bin = ?",
            [$langDesc]
        );
    }

    /**
     * @return array<string, array{string, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function patternProvider(): array
    {
        return [
            'language code accepted' => ['en', '^[a-z]{2}$', true],
            'uppercase language code rejected' => ['EN', '^[a-z]{2}$', false],
            'three letters rejected' => ['eng', '^[a-z]{2}$', false],
            'slash in pattern is escaped' => ['a/b', 'a/b', true],
            'escaped slash is literal' => ['aXb', 'a/b', false],
            'empty pattern matches anything' => ['anything', '', true],
        ];
    }

    /**
     * check_pattern() through the legacy delegator.
     */
    #[Test]
    #[DataProvider('patternProvider')]
    public function testLegacyCheckPattern(string $data, string $pattern, bool $expected): void
    {
        $this->assertSame($expected, check_pattern($data, $pattern));
    }

    /**
     * Same cases, called on LanguageService directly.
     */
    #[Test]
    #[DataProvider('patternProvider')]
    public function testServiceCheckPattern(string $data, string $pattern, bool $expected): void
    {
        $this->assertSame($expected, LanguageService::checkPattern($data, $pattern));
    }

    /**
     * A non-string argument stops at the delegator: nothing is written. (Before the move, an array
     * definition reached the query binding and failed there.)
     */
    #[Test]
    public function testDelegatorIgnoresNonStringArguments(): void
    {
        $desc = self::TAG . 'full-array';
        $cons = self::TAG . 'const-full-array';

        insert_language_log($desc, 'xx', $cons, ['not', 'a', 'string']);

        $this->assertCount(0, $this->fetchRows($desc, $cons));
    }

    /**
     * NEW LANGUAGE branch (constant_name === ''): its duplicate guard compares constant_name to
     * '', but the freshly-inserted row's constant_name is NULL (nullable column, defaults to NULL), so
     * the guard never matches and a repeat call always inserts a second row. Preserved as-is —
     * not a behavior to fix while moving the code.
     */
    #[Test]
    public function testLegacyNewLanguageRepeatInsertsDuplicateRow(): void
    {
        $desc = self::TAG . 'lang-a';

        insert_language_log($desc, 'xx', '', '');
        insert_language_log($desc, 'xx', '', '');

        $rows = $this->fetchRowsByDescription($desc);
        $this->assertCount(2, $rows);
        $this->assertSame('xx', $rows[0]['lang_code']);
    }

    /**
     * Same case, called on LanguageService directly.
     */
    #[Test]
    public function testServiceNewLanguageRepeatInsertsDuplicateRow(): void
    {
        $desc = self::TAG . 'lang-b';

        LanguageService::insertLanguageLog($desc, 'xx', '', '');
        LanguageService::insertLanguageLog($desc, 'xx', '', '');

        $rows = $this->fetchRowsByDescription($desc);
        $this->assertCount(2, $rows);
        $this->assertSame('xx', $rows[0]['lang_code']);
    }

    /**
     * NEW CONSTANT branch (lang_description === ''): a new constant_name is inserted, and
     * repeating the same call does not create a duplicate row.
     */
    #[Test]
    public function testLegacyNewConstantInsertedOnceNotDuplicated(): void
    {
        $cons = self::TAG . 'const-a';

        insert_language_log('', '', $cons, '');
        insert_language_log('', '', $cons, '');

        $rows = $this->fetchRows('', $cons);
        $this->assertCount(1, $rows);
    }

    /**
     * Same case, called on LanguageService directly.
     */
    #[Test]
    public function testServiceNewConstantInsertedOnceNotDuplicated(): void
    {
        $cons = self::TAG . 'const-b';

        LanguageService::insertLanguageLog('', '', $cons, '');
        LanguageService::insertLanguageLog('', '', $cons, '');

        $rows = $this->fetchRows('', $cons);
        $this->assertCount(1, $rows);
    }

    /**
     * NEW CONSTANT branch: the duplicate guard only looks at constant-only rows (empty
     * lang_description), so a full entry for the same constant does not suppress the insert.
     */
    #[Test]
    public function testServiceNewConstantInsertedEvenWhenFullEntryExists(): void
    {
        $desc = self::TAG . 'full-const-g';
        $cons = self::TAG . 'const-g';

        LanguageService::insertLanguageLog($desc, 'xx', $cons, 'definition');
        LanguageService::insertLanguageLog('', '', $cons, '');

        $this->assertCount(1, $this->fetchRows($desc, $cons));
        $this->assertCount(1, $this->fetchRows('', $cons));
    }

    /**
     * FULL ENTRY branch: an identical repeat call (same description, constant and definition)
     * makes no change.
     */
    #[Test]
    public function testLegacyFullEntryIdenticalRepeatMakesNoChange(): void
    {
        $desc = self::TAG . 'full-a';
        $cons = self::TAG . 'const-full-a';

        insert_language_log($desc, 'xx', $cons, 'definition one');
        insert_language_log($desc, 'xx', $cons, 'definition one');

        $rows = $this->fetchRows($desc, $cons);
        $this->assertCount(1, $rows);
        $this->assertSame('definition one', $rows[0]['definition']);
    }

    /**
     * Same case, called on LanguageService directly.
     */
    #[Test]
    public function testServiceFullEntryIdenticalRepeatMakesNoChange(): void
    {
        $desc = self::TAG . 'full-b';
        $cons = self::TAG . 'const-full-b';

        LanguageService::insertLanguageLog($desc, 'xx', $cons, 'definition one');
        LanguageService::insertLanguageLog($desc, 'xx', $cons, 'definition one');

        $rows = $this->fetchRows($desc, $cons);
        $this->assertCount(1, $rows);
        $this->assertSame('definition one', $rows[0]['definition']);
    }

    /**
     * FULL ENTRY branch: same description and constant, different definition, updates the
     * existing row's definition rather than inserting a second row.
     */
    #[Test]
    public function testLegacyFullEntrySameKeyDifferentDefinitionUpdates(): void
    {
        $desc = self::TAG . 'full-c';
        $cons = self::TAG . 'const-full-c';

        insert_language_log($desc, 'xx', $cons, 'definition one');
        insert_language_log($desc, 'xx', $cons, 'definition two');

        $rows = $this->fetchRows($desc, $cons);
        $this->assertCount(1, $rows);
        $this->assertSame('definition two', $rows[0]['definition']);
    }

    /**
     * Same case, called on LanguageService directly.
     */
    #[Test]
    public function testServiceFullEntrySameKeyDifferentDefinitionUpdates(): void
    {
        $desc = self::TAG . 'full-d';
        $cons = self::TAG . 'const-full-d';

        LanguageService::insertLanguageLog($desc, 'xx', $cons, 'definition one');
        LanguageService::insertLanguageLog($desc, 'xx', $cons, 'definition two');

        $rows = $this->fetchRows($desc, $cons);
        $this->assertCount(1, $rows);
        $this->assertSame('definition two', $rows[0]['definition']);
    }

    /**
     * FULL ENTRY branch: a different description/constant/definition combination is a distinct
     * entry, inserted as its own row.
     */
    #[Test]
    public function testLegacyFullEntryDifferentKeyInsertsSeparateRow(): void
    {
        $descA = self::TAG . 'full-e-a';
        $descB = self::TAG . 'full-e-b';
        $cons = self::TAG . 'const-full-e';

        insert_language_log($descA, 'xx', $cons, 'definition one');
        insert_language_log($descB, 'xx', $cons, 'definition two');

        $this->assertCount(1, $this->fetchRows($descA, $cons));
        $this->assertCount(1, $this->fetchRows($descB, $cons));
    }

    /**
     * Same case, called on LanguageService directly.
     */
    #[Test]
    public function testServiceFullEntryDifferentKeyInsertsSeparateRow(): void
    {
        $descA = self::TAG . 'full-f-a';
        $descB = self::TAG . 'full-f-b';
        $cons = self::TAG . 'const-full-f';

        LanguageService::insertLanguageLog($descA, 'xx', $cons, 'definition one');
        LanguageService::insertLanguageLog($descB, 'xx', $cons, 'definition two');

        $this->assertCount(1, $this->fetchRows($descA, $cons));
        $this->assertCount(1, $this->fetchRows($descB, $cons));
    }

    /**
     * The COLLATE utf8mb4_bin comparisons are case-sensitive: a description differing only in
     * case is a distinct entry, not a repeat of the existing one.
     */
    #[Test]
    public function testLegacyCaseSensitivityTreatsCaseDifferenceAsDistinct(): void
    {
        $descLower = self::TAG . 'case-a';
        $descUpper = self::TAG . 'CASE-A';
        $cons = self::TAG . 'const-case-a';

        insert_language_log($descLower, 'xx', $cons, 'definition');
        insert_language_log($descUpper, 'xx', $cons, 'definition');

        $this->assertCount(1, $this->fetchRows($descLower, $cons));
        $this->assertCount(1, $this->fetchRows($descUpper, $cons));
    }

    /**
     * Same case, called on LanguageService directly.
     */
    #[Test]
    public function testServiceCaseSensitivityTreatsCaseDifferenceAsDistinct(): void
    {
        $descLower = self::TAG . 'case-b';
        $descUpper = self::TAG . 'CASE-B';
        $cons = self::TAG . 'const-case-b';

        LanguageService::insertLanguageLog($descLower, 'xx', $cons, 'definition');
        LanguageService::insertLanguageLog($descUpper, 'xx', $cons, 'definition');

        $this->assertCount(1, $this->fetchRows($descLower, $cons));
        $this->assertCount(1, $this->fetchRows($descUpper, $cons));
    }
}
