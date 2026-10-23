<?php

/**
 * What a saved QuestionnaireResponse.questionnaire names.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

final class QuestionnaireCanonical
{
    /**
     * The Questionnaire's own canonical url, with its version when it has one (url|version, as
     * the runtime sets it), else this server's copy of it.
     *
     * @param mixed  $url      Questionnaire.url
     * @param mixed  $version  Questionnaire.version
     * @param string $fallback this server's Questionnaire address
     */
    public static function forResponse(mixed $url, mixed $version, string $fallback): string
    {
        if (!is_string($url) || trim($url) === '') {
            return $fallback;
        }
        $canonical = trim($url);
        if (is_string($version) && trim($version) !== '') {
            $canonical .= '|' . trim($version);
        }
        return $canonical;
    }
}
