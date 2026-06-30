<?php

/**
 * Isolated tests for the UDS Table 3B Line 12 language-barrier rule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\LanguageBarrierRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LanguageBarrierRuleTest extends TestCase
{
    #[DataProvider('barrierProvider')]
    public function testBestServedInNonEnglishLanguage(
        ?string $language,
        ?string $interpreterNeeded,
        bool $expected,
    ): void {
        $rule = new LanguageBarrierRule();

        self::assertSame($expected, $rule->bestServedInNonEnglishLanguage($language, $interpreterNeeded));
    }

    /**
     * @return array<string, array{?string, ?string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function barrierProvider(): array
    {
        return [
            'interpreter needed is decisive even for english' => ['english', 'yes', true],
            'interpreter not needed, english speaker' => ['english', 'no', false],
            'non-english language counts' => ['spanish', 'no', true],
            'non-english language with no interpreter flag' => ['vietnamese', null, true],
            'english with no interpreter flag' => ['english', null, false],
            'declined language is not a barrier' => ['decline_to_specify', null, false],
            'blank language is not a barrier' => ['', null, false],
            'null language, no flag' => [null, null, false],
            'null language but interpreter needed' => [null, 'yes', true],
        ];
    }
}
