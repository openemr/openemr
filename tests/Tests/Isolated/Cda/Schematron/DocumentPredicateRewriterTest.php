<?php

/**
 * DocumentPredicateRewriter isolated test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Schematron;

use OpenEMR\Services\Cda\Schematron\ArrayVocabularyLookup;
use OpenEMR\Services\Cda\Schematron\DocumentPredicateRewriter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DocumentPredicateRewriterTest extends TestCase
{
    private DocumentPredicateRewriter $rewriter;

    protected function setUp(): void
    {
        $this->rewriter = new DocumentPredicateRewriter(new ArrayVocabularyLookup([
            '2.16.840.1.113883.11.20.9.18' => ['EVN', 'INT'],
            '2.16.840.1.113883.11.20.9.19' => ['completed', 'active', "with'apostrophe"],
            '9.9.9.99' => [],
        ]));
    }

    public function testPassesThroughTestsWithoutDocumentPredicate(): void
    {
        $test = 'cda:foo/@bar and count(cda:baz) > 0';
        self::assertSame($test, $this->rewriter->rewrite($test));
    }

    public function testRewritesSingleOidReference(): void
    {
        $test = "@moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.18']/voc:code/@value";
        self::assertSame(
            "(@moodCode='EVN' or @moodCode='INT')",
            $this->rewriter->rewrite($test),
        );
    }

    public function testRewritesInsideLargerExpression(): void
    {
        $test = "@moodCode and @moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.18']/voc:code/@value";
        self::assertSame(
            "@moodCode and (@moodCode='EVN' or @moodCode='INT')",
            $this->rewriter->rewrite($test),
        );
    }

    public function testEmptyOidValuesEmitFalse(): void
    {
        $test = "@x=document('voc.xml')/voc:systems/voc:system[@valueSetOid='9.9.9.99']/voc:code/@value";
        self::assertSame('(false())', $this->rewriter->rewrite($test));
    }

    public function testValueContainingApostropheWrappedInDoubleQuotes(): void
    {
        $test = "@x=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.19']/voc:code/@value";
        $rewritten = $this->rewriter->rewrite($test);
        self::assertStringContainsString("\"with'apostrophe\"", $rewritten);
    }

    public function testMissingOidThrows(): void
    {
        $test = "@x=document('voc.xml')/voc:systems/voc:system[@valueSetOid='9.9.9.9']/voc:code/@value";
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Vocabulary OID not found: 9.9.9.9');
        $this->rewriter->rewrite($test);
    }

    public function testUnhandledDocumentShapeThrows(): void
    {
        $test = "count(document('voc.xml')/voc:systems)";
        $this->expectException(RuntimeException::class);
        $this->rewriter->rewrite($test);
    }

    /**
     * @dataProvider xpathLiteralProvider
     */
    public function testXpathLiteralEscaping(string $input, string $expected): void
    {
        self::assertSame($expected, DocumentPredicateRewriter::xpathLit($input));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function xpathLiteralProvider(): array
    {
        return [
            'plain' => ['foo', "'foo'"],
            'with-apostrophe' => ["can't", '"can\'t"'],
            'with-double-quote' => ['he said "hi"', "'he said \"hi\"'"],
            'with-both' => ["mix'ed\"quotes", "concat('mix',\"'\",'ed\"quotes')"],
        ];
    }
}
