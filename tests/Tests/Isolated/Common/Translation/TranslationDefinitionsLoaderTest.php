<?php

/**
 * Isolated tests for TranslationDefinitionsLoader.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Translation;

use OpenEMR\Common\Translation\TranslationDefinitionsLoader;
use PHPUnit\Framework\TestCase;

final class TranslationDefinitionsLoaderTest extends TestCase
{
    /** @var list<int> Language ids read from the "database" */
    private array $databaseReads = [];

    private function database(): \Closure
    {
        return function (int $langId): array {
            $this->databaseReads[] = $langId;
            return ['Save' => "Guardar $langId"];
        };
    }

    private function memoryStore(): InMemoryTranslationStore
    {
        return new InMemoryTranslationStore();
    }

    public function testWithoutASharedStoreEveryLoadReadsTheDatabase(): void
    {
        $loader = new TranslationDefinitionsLoader(null, 'p:', $this->database());

        $this->assertSame(['Save' => 'Guardar 3'], $loader->load(3));
        $loader->load(3);

        $this->assertSame([3, 3], $this->databaseReads);
    }

    public function testAMissReadsTheDatabaseOnceAndStoresWithTheTtl(): void
    {
        $store = $this->memoryStore();
        $loader = new TranslationDefinitionsLoader($store, 'p:', $this->database());

        $this->assertSame(['Save' => 'Guardar 3'], $loader->load(3));
        $this->assertSame(['Save' => 'Guardar 3'], $loader->load(3));

        $this->assertSame([3], $this->databaseReads);
        $this->assertSame(
            ['p:3' => [['Save' => 'Guardar 3'], TranslationDefinitionsLoader::SHARED_TTL_SECONDS]],
            $store->entries
        );
    }

    public function testLanguagesAreCachedSeparately(): void
    {
        $loader = new TranslationDefinitionsLoader($this->memoryStore(), 'p:', $this->database());

        $loader->load(3);
        $this->assertSame(['Save' => 'Guardar 4'], $loader->load(4));
        $this->assertSame([3, 4], $this->databaseReads);
    }

    public function testANewVersionMissesTheOldEntry(): void
    {
        $store = $this->memoryStore();
        (new TranslationDefinitionsLoader($store, TranslationDefinitionsLoader::keyPrefix('/sites/default', 'a'), $this->database()))->load(3);
        (new TranslationDefinitionsLoader($store, TranslationDefinitionsLoader::keyPrefix('/sites/default', 'b'), $this->database()))->load(3);

        $this->assertSame([3, 3], $this->databaseReads);
    }

    public function testKeyPrefixSeparatesSitesAndInstallsAndDefaultsTheVersion(): void
    {
        $this->assertNotSame(
            TranslationDefinitionsLoader::keyPrefix('/var/www/a/sites/default', '1'),
            TranslationDefinitionsLoader::keyPrefix('/var/www/b/sites/default', '1')
        );
        $this->assertNotSame(
            TranslationDefinitionsLoader::keyPrefix('/var/www/a/sites/default', '1'),
            TranslationDefinitionsLoader::keyPrefix('/var/www/a/sites/clinic2', '1')
        );
        $this->assertSame(
            TranslationDefinitionsLoader::keyPrefix('/s', '0'),
            TranslationDefinitionsLoader::keyPrefix('/s', '')
        );
    }
}
