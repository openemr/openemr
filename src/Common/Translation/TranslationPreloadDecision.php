<?php

/**
 * Whether to preload a language's translations for this request.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

final readonly class TranslationPreloadDecision
{
    /**
     * @param array{lang_id: int, preload: bool}|null $remember Value to keep in
     *        the session so later requests skip the definition count; null when
     *        nothing new was learned.
     */
    public function __construct(
        public bool $preload,
        public ?array $remember = null,
    ) {
    }
}
