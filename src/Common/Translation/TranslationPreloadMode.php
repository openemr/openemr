<?php

/**
 * Value of the translation_preload_cache global.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * Backed by the stored global value. '0' and '1' are the values the global
 * held when it was a yes/no setting, so those rows keep their meaning.
 */
enum TranslationPreloadMode: string
{
    /** Look up each string with its own query. */
    case Off = '0';

    /** Preload only languages with few definitions (English has none). */
    case Auto = 'auto';

    /** Preload every language. */
    case Always = '1';

    /**
     * An unknown stored value keeps the per-string lookups, which work for
     * every language.
     */
    public static function fromGlobal(string $value): self
    {
        return self::tryFrom($value) ?? self::Off;
    }
}
