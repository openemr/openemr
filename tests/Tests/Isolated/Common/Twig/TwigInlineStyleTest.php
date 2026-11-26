<?php

/**
 * Keep inline style counts from growing in Twig templates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Humanifest Bot
 * @copyright Copyright (c) 2026 Humanifest
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Twig;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Twig\Source;
use Twig\Token;

#[Group('isolated')]
#[Group('twig')]
class TwigInlineStyleTest extends TestCase
{
    private const BASELINE = __DIR__ . '/fixtures/inline-style-baseline.json';

    // These outputs need inline styles. Keep exemptions explicit so an unrelated
    // template cannot opt out merely by moving into a broadly exempt directory.
    private const EXEMPT_TEMPLATES = [
        'interface/modules/custom_modules/oe-module-comlink-telehealth/templates/comlink/emails/telehealth-invitation-existing.html.twig',
        'interface/modules/custom_modules/oe-module-comlink-telehealth/templates/comlink/emails/telehealth-invitation-new.html.twig',
        'templates/calendar/default/views/day_print/outlook_ajax_template.html.twig',
        'templates/calendar/default/views/week_print/outlook_ajax_template.html.twig',
        'templates/calendar/default/views/month_print/outlook_ajax_template.html.twig',
    ];

    #[Test]
    public function inlineStylesMatchBaseline(): void
    {
        self::assertFileExists(self::BASELINE);
        $json = file_get_contents(self::BASELINE);
        self::assertIsString($json);
        $baseline = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($baseline);
        $expected = [];
        foreach ($baseline as $path => $count) {
            self::assertIsString($path);
            self::assertIsInt($count);
            self::assertGreaterThan(0, $count, "Remove zero-count baseline entries: $path");
            $expected[$path] = $count;
        }

        $actual = self::templateStyleCounts();
        $paths = array_unique([...array_keys($expected), ...array_keys($actual)]);
        sort($paths, SORT_STRING);
        $changes = [];
        foreach ($paths as $path) {
            $old = $expected[$path] ?? 0;
            $new = $actual[$path] ?? 0;
            if ($old !== $new) {
                $changes[] = "$path: baseline $old, found $new";
            }
        }

        self::assertSame([], $changes, implode("\n", $changes) . "\n"
            . 'Use Bootstrap utility classes or the page stylesheet instead of a style attribute. '
            . 'Computed attribute values still count. Lower the baseline when styles are removed; '
            . 'do not raise it to admit new inline styles.');
    }

    #[Test]
    public function exemptionPathsExist(): void
    {
        $templates = [];
        foreach (TwigTemplateCompilationTest::twigTemplateProvider() as $path => $_template) {
            $templates[str_replace('\\', '/', $path)] = true;
        }
        foreach (self::EXEMPT_TEMPLATES as $path) {
            self::assertArrayHasKey($path, $templates, "Remove or correct stale exemption: $path");
        }
    }

    #[Test]
    #[DataProvider('inlineStyleCases')]
    public function countsOnlyTwigTextStyles(string $code, int $expected): void
    {
        self::assertSame($expected, self::countInlineStyles($code, 'fixture'));
    }

    /** @return array<string, positive-int> */
    public static function templateStyleCounts(): array
    {
        $counts = [];
        foreach (TwigTemplateCompilationTest::twigTemplateProvider() as $path => [, $file]) {
            $path = str_replace('\\', '/', $path);
            if (in_array($path, self::EXEMPT_TEMPLATES, true)) {
                continue;
            }
            $code = file_get_contents($file);
            self::assertIsString($code, "Failed to read $path");
            $count = self::countInlineStyles($code, $path);
            if ($count > 0) {
                $counts[$path] = $count;
            }
        }
        ksort($counts, SORT_STRING);
        return $counts;
    }

    public static function countInlineStyles(string $code, string $name): int
    {
        $lexer = new Lexer(new Environment(new ArrayLoader()));
        $stream = $lexer->tokenize(new Source($code, $name));
        $count = 0;
        while (!$stream->isEOF()) {
            $token = $stream->getCurrent();
            if ($token->test(Token::TEXT_TYPE)) {
                $text = $token->getValue();
                self::assertIsString($text);
                // A boundary prevents data-style and similarly named attributes
                // in the same text token from being mistaken for style.
                $matches = preg_match_all('/(?<![a-z0-9_:-])style\s*=/i', $text);
                self::assertNotFalse($matches);
                $count += $matches;
            }
            $stream->next();
        }
        return $count;
    }

    /**
     * @return iterable<string, array{string, int}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation.
     */
    public static function inlineStyleCases(): iterable
    {
        yield 'stylesheet class' => ['<div class="wide">text</div>', 0];
        yield 'literal style' => ['<div style="width: 5em">text</div>', 1];
        yield 'uppercase and whitespace' => ['<div STYLE = "width: 5em">text</div>', 1];
        yield 'computed value' => ['<div style="{{ width }}">text</div>', 1];
        yield 'multiple attributes' => ['<div style="a:b"></div><span style="c:d"></span>', 2];
        yield 'data attributes' => ['<div data-style="a:b" aria-style="c:d"></div>', 0];
        // The requested contract counts literal TEXT, without rendering HTML.
        yield 'literal text contract' => ['Example: style="a:b"', 1];
        yield 'Twig comment' => ['{# <div style="a:b"></div> #}<div></div>', 0];
        yield 'Twig expression' => ['{{ \'<div style="a:b"></div>\' }}', 0];
        yield 'Twig statement' => ['{% set markup = \'<div style="a:b"></div>\' %}', 0];
        yield 'mixed code and markup' => ['{% set x = \'style="a:b"\' %}<div style="{{ x }}"></div>', 1];
        yield 'multiline' => ["<div\n style = \"width:5em\">text</div>", 1];
        yield 'attribute after Twig condition' => ['<div {% if enabled %}style="a:b"{% endif %}></div>', 1];
    }
}
