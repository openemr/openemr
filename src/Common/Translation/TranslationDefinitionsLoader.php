<?php

/**
 * Loads a language's definitions, through a shared cache when one is available.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

final readonly class TranslationDefinitionsLoader
{
    /**
     * How long a cached language lives. Edits made through OpenEMR's
     * translation screens take effect at once (they bump the cache version);
     * this bounds how long a translation set loaded straight into the
     * database with SQL can be served stale.
     */
    public const SHARED_TTL_SECONDS = 600;

    /**
     * @param \Closure(int): array<array-key, string> $loadFromDatabase
     * @param string $keyPrefix Identifies the install, site and cache version;
     *        the language id is appended.
     */
    public function __construct(
        private ?SharedTranslationStore $shared,
        private string $keyPrefix,
        private \Closure $loadFromDatabase,
    ) {
    }

    /**
     * @return array<array-key, string>
     */
    public function load(int $langId): array
    {
        if ($this->shared === null) {
            return ($this->loadFromDatabase)($langId);
        }
        $key = $this->keyPrefix . $langId;
        $definitions = $this->shared->fetch($key);
        if ($definitions === null) {
            $definitions = ($this->loadFromDatabase)($langId);
            $this->shared->store($key, $definitions, self::SHARED_TTL_SECONDS);
        }
        return $definitions;
    }

    /**
     * Key prefix for one install's site at one cache version. The site
     * directory is a full path, so two installs on the same server (which
     * share APCu) never read each other's entries.
     */
    public static function keyPrefix(string $siteDirectory, string $version): string
    {
        return 'openemr:translations:' . md5($siteDirectory) . ':' . ($version === '' ? '0' : $version) . ':';
    }
}
