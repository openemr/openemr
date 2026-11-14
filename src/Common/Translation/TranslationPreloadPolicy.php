<?php

/**
 * Decides whether a request preloads its language's translations.
 *
 * Preloading reads the whole language in one query, so every xl() call
 * becomes an array lookup. That wins for English and other languages with
 * few definitions (about 0.3 ms for English versus up to ~1,500 single-row
 * queries on a heavy page), but a fully translated language costs about
 * 40 ms and 3 MB per request, more than the ~20 lookups of a light page or
 * AJAX call. Without a shared cache, Auto mode therefore preloads only
 * languages at or below AUTO_MAX_DEFINITIONS, and counts a language's
 * definitions once per session. With a shared cache (APCu) a full language
 * copies in for a few milliseconds, so Auto preloads every language.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

final readonly class TranslationPreloadPolicy
{
    /**
     * Largest language Auto preloads. Loading costs about 3.3 us per
     * definition and a single lookup about 0.25-0.5 ms, so preloading beats
     * the ~20 lookups of a light page up to roughly 1,500-3,000 definitions.
     */
    public const AUTO_MAX_DEFINITIONS = 1000;

    /** Session key holding the remembered Auto decision. */
    public const SESSION_KEY = 'translation_preload';

    /**
     * @param \Closure(int): int $countDefinitions Number of definitions a
     *        language id has.
     */
    public function __construct(private \Closure $countDefinitions)
    {
    }

    /**
     * @param mixed $remembered The session value stored under SESSION_KEY by
     *        an earlier request, or null.
     */
    public function decide(
        TranslationPreloadMode $mode,
        int $langId,
        mixed $remembered,
        bool $sharedCacheAvailable = false,
    ): TranslationPreloadDecision {
        return match ($mode) {
            TranslationPreloadMode::Off => new TranslationPreloadDecision(false),
            TranslationPreloadMode::Always => new TranslationPreloadDecision(true),
            // With a shared cache a full language costs a few milliseconds to
            // copy in, so Auto preloads every language without counting.
            TranslationPreloadMode::Auto => $sharedCacheAvailable
                ? new TranslationPreloadDecision(true)
                : $this->decideAuto($langId, $remembered),
        };
    }

    private function decideAuto(int $langId, mixed $remembered): TranslationPreloadDecision
    {
        if (
            is_array($remembered)
            && ($remembered['lang_id'] ?? null) === $langId
            && is_bool($remembered['preload'] ?? null)
        ) {
            return new TranslationPreloadDecision($remembered['preload']);
        }

        $preload = ($this->countDefinitions)($langId) <= self::AUTO_MAX_DEFINITIONS;
        return new TranslationPreloadDecision($preload, ['lang_id' => $langId, 'preload' => $preload]);
    }
}
