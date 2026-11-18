<?php

/**
 * Database tests for TranslationCache and the Auto preload decision.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Translation;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Translation\TranslationCache;
use OpenEMR\Common\Translation\TranslationPreloadMode;
use OpenEMR\Common\Translation\TranslationPreloadPolicy;
use PHPUnit\Framework\TestCase;

final class TranslationCacheTest extends TestCase
{
    private const ENGLISH = 1;

    protected function setUp(): void
    {
        TranslationCache::reset();
    }

    protected function tearDown(): void
    {
        TranslationCache::reset();
    }

    /**
     * The installed language with the most definitions, or a skip when the
     * database has no translations loaded.
     *
     * @return array{int, int} Language id and definition count
     */
    private function largestLanguage(): array
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT lang_id, COUNT(*) AS cnt FROM lang_definitions GROUP BY lang_id ORDER BY cnt DESC LIMIT 1'
        );
        $langId = $rows[0]['lang_id'] ?? null;
        $count = $rows[0]['cnt'] ?? null;
        if (!is_numeric($langId) || !is_numeric($count)) {
            $this->markTestSkipped('No translations are loaded in this database.');
        }
        return [(int) $langId, (int) $count];
    }

    public function testEnglishHasNoDefinitions(): void
    {
        $this->assertSame(0, TranslationCache::countDefinitions(self::ENGLISH));
    }

    public function testCountMatchesTheRowsWarmLoads(): void
    {
        [$langId, $count] = $this->largestLanguage();

        $this->assertSame($count, TranslationCache::countDefinitions($langId));

        TranslationCache::warm($langId);
        $constant = QueryUtils::fetchRecordsNoLog(
            'SELECT lang_constants.constant_name, lang_definitions.definition'
            . ' FROM lang_definitions JOIN lang_constants USING (cons_id)'
            . ' WHERE lang_definitions.lang_id = ? LIMIT 1',
            [$langId]
        )[0];
        $this->assertIsString($constant['constant_name']);
        $this->assertTrue(TranslationCache::has($langId, $constant['constant_name']));
        $this->assertSame($constant['definition'], TranslationCache::get($langId, $constant['constant_name']));
    }

    public function testWarmingOneLanguageDoesNotMarkAnotherWarmed(): void
    {
        [$langId] = $this->largestLanguage();

        TranslationCache::warm(self::ENGLISH);

        $this->assertTrue(TranslationCache::isWarmed(self::ENGLISH));
        $this->assertFalse(TranslationCache::isWarmed($langId));
    }

    public function testLoadDefinitionsMatchesWhatWarmCaches(): void
    {
        [$langId] = $this->largestLanguage();
        // A few constant names exist twice in lang_constants; they share one key.
        $distinct = QueryUtils::fetchRecordsNoLog(
            'SELECT COUNT(DISTINCT BINARY lang_constants.constant_name) AS cnt'
            . ' FROM lang_definitions JOIN lang_constants USING (cons_id)'
            . ' WHERE lang_definitions.lang_id = ?',
            [$langId]
        )[0]['cnt'] ?? null;
        $this->assertIsNumeric($distinct);

        $definitions = TranslationCache::loadDefinitions($langId);
        $this->assertCount((int) $distinct, $definitions);

        TranslationCache::warmWith($langId, ['Only' => 'Solo']);
        $this->assertTrue(TranslationCache::isWarmed($langId));
        $this->assertSame('Solo', TranslationCache::get($langId, 'Only'));
        $this->assertFalse(TranslationCache::has($langId, 'Save'));
    }

    public function testBumpSharedVersionChangesTheStoredVersion(): void
    {
        $read = static fn(): mixed => QueryUtils::fetchRecordsNoLog(
            'SELECT gl_value FROM globals WHERE gl_name = ? AND gl_index = 0',
            [TranslationCache::VERSION_GLOBAL]
        )[0]['gl_value'] ?? null;
        $before = $read();

        try {
            TranslationCache::bumpSharedVersion();
            $first = $read();
            TranslationCache::bumpSharedVersion();
            $second = $read();

            $this->assertIsString($first);
            $this->assertNotSame($before, $first);
            $this->assertNotSame($first, $second);
        } finally {
            if (is_string($before)) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE globals SET gl_value = ? WHERE gl_name = ? AND gl_index = 0',
                    [$before, TranslationCache::VERSION_GLOBAL]
                );
            } else {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM globals WHERE gl_name = ?',
                    [TranslationCache::VERSION_GLOBAL]
                );
            }
        }
    }

    public function testAutoPreloadsEnglishButNotAFullLanguage(): void
    {
        [$langId, $count] = $this->largestLanguage();
        if ($count <= TranslationPreloadPolicy::AUTO_MAX_DEFINITIONS) {
            $this->markTestSkipped('No installed language is above the Auto threshold.');
        }
        $policy = new TranslationPreloadPolicy(TranslationCache::countDefinitions(...));

        $this->assertTrue($policy->decide(TranslationPreloadMode::Auto, self::ENGLISH, null)->preload);
        $this->assertFalse($policy->decide(TranslationPreloadMode::Auto, $langId, null)->preload);
    }
}
