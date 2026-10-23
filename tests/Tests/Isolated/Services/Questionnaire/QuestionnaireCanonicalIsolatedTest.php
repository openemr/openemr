<?php

/**
 * What a saved QuestionnaireResponse.questionnaire names: the Questionnaire's canonical
 * url|version, else the server's copy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Questionnaire;

use OpenEMR\Services\Questionnaire\QuestionnaireCanonical;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QuestionnaireCanonicalIsolatedTest extends TestCase
{
    private const FALLBACK = 'https://example.org/apis/default/fhir/Questionnaire/abc';

    /**
     * @return array<string, array{mixed, mixed, string}>
     */
    public static function cases(): array
    {
        return [
            'url and version' => ['http://loinc.org/q/44249-1', '2.73', 'http://loinc.org/q/44249-1|2.73'],
            'url without a version' => ['http://loinc.org/q/44249-1', null, 'http://loinc.org/q/44249-1'],
            'blank version' => ['http://loinc.org/q/44249-1', '  ', 'http://loinc.org/q/44249-1'],
            'spaces trimmed' => [' https://example.org/q/x ', ' 3 ', 'https://example.org/q/x|3'],
            'no url' => [null, '2', self::FALLBACK],
            'blank url' => ['', '2', self::FALLBACK],
            'a url that is not a string' => [['x'], null, self::FALLBACK],
        ];
    }

    #[DataProvider('cases')]
    public function testTheResponseNamesItsQuestionnaire(mixed $url, mixed $version, string $expected): void
    {
        self::assertSame($expected, QuestionnaireCanonical::forResponse($url, $version, self::FALLBACK));
    }
}
