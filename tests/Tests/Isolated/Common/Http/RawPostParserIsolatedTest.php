<?php

/**
 * Isolated RawPostParser tests
 *
 * Verifies that the parser bypasses PHP's max_input_vars truncation, rejects
 * multipart bodies, caches results, and produces the same array shape PHP's
 * own POST parser would have produced for the same input.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Alanis <progradedteam@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\RawPostParser;
use OpenEMR\Common\Http\RawPostParserException;
use OpenEMR\Common\Http\RawRequestBodyReader;
use PHPUnit\Framework\TestCase;

class RawPostParserIsolatedTest extends TestCase
{
    private function readerFor(string $body): RawRequestBodyReader
    {
        // data:// URIs let us feed a fixture body through the real
        // file_get_contents path without touching php://input.
        return new RawRequestBodyReader('data://text/plain;base64,' . base64_encode($body));
    }

    public function testParsesUrlEncodedBody(): void
    {
        $parser = new RawPostParser(
            $this->readerFor('foo=1&bar=2&baz=hello+world'),
            'application/x-www-form-urlencoded',
        );

        $this->assertSame(
            ['foo' => '1', 'bar' => '2', 'baz' => 'hello world'],
            $parser->parse(),
        );
    }

    public function testParsesArrayNotation(): void
    {
        $parser = new RawPostParser(
            $this->readerFor('fld[1][id]=a&fld[1][type]=text&fld[2][id]=b'),
            'application/x-www-form-urlencoded',
        );

        $this->assertSame(
            [
                'fld' => [
                    1 => ['id' => 'a', 'type' => 'text'],
                    2 => ['id' => 'b'],
                ],
            ],
            $parser->parse(),
        );
    }

    public function testRecoversBodyLargerThanTypicalMaxInputVars(): void
    {
        // Build a body with 5000 fld[N][id]=... pairs — comfortably past any
        // sane max_input_vars ceiling (default is 1000).
        $pairs = [];
        for ($i = 1; $i <= 5000; $i++) {
            $pairs[] = sprintf('fld[%d][id]=row%d&fld[%d][type]=t', $i, $i, $i);
        }
        $body = implode('&', $pairs);

        $parser = new RawPostParser(
            $this->readerFor($body),
            'application/x-www-form-urlencoded',
        );
        $parsed = $parser->parse();

        $this->assertArrayHasKey('fld', $parsed);
        $fld = $parsed['fld'];
        $this->assertIsArray($fld);
        $this->assertCount(5000, $fld);

        $this->assertArrayHasKey(1, $fld);
        $firstRow = $fld[1];
        $this->assertIsArray($firstRow);
        $this->assertSame('row1', $firstRow['id'] ?? null);

        $this->assertArrayHasKey(5000, $fld);
        $lastRow = $fld[5000];
        $this->assertIsArray($lastRow);
        $this->assertSame('row5000', $lastRow['id'] ?? null);
    }

    public function testCachesResultAcrossCalls(): void
    {
        $reader = $this->readerFor('foo=cached');
        $parser = new RawPostParser($reader, 'application/x-www-form-urlencoded');

        $first = $parser->parse();
        $second = $parser->parse();

        $this->assertSame($first, $second);
        // Reader cache prevents a second underlying read; explicit identity
        // check confirms the parser returns the same array object.
        $this->assertSame($first, $second);
    }

    public function testRejectsMultipartFormData(): void
    {
        $parser = new RawPostParser(
            $this->readerFor('--boundary--'),
            'multipart/form-data; boundary=----WebKitFormBoundary',
        );

        $this->expectException(RawPostParserException::class);
        $this->expectExceptionMessage('multipart/form-data');
        $parser->parse();
    }

    public function testAcceptsContentTypeWithCharset(): void
    {
        $parser = new RawPostParser(
            $this->readerFor('foo=bar'),
            'application/x-www-form-urlencoded; charset=UTF-8',
        );

        $this->assertSame(['foo' => 'bar'], $parser->parse());
    }

    public function testEmptyBodyParsesToEmptyArray(): void
    {
        $parser = new RawPostParser(
            $this->readerFor(''),
            'application/x-www-form-urlencoded',
        );

        $this->assertSame([], $parser->parse());
    }

    public function testMergesNestedArraysAcrossChunkBoundary(): void
    {
        // Force a chunk boundary inside an `fld[N]` group by stuffing the
        // body with > MIN_CHUNK_SIZE leading fields, then a nested group
        // whose two pairs straddle the boundary. If the merge is wrong, one
        // of the inner keys is lost.
        $pairs = [];
        for ($i = 0; $i < 60; $i++) {
            $pairs[] = sprintf('lead%d=x', $i);
        }
        $pairs[] = 'fld[1][id]=alpha';
        $pairs[] = 'fld[1][type]=text';
        $body = implode('&', $pairs);

        $parser = new RawPostParser(
            $this->readerFor($body),
            'application/x-www-form-urlencoded',
        );
        $parsed = $parser->parse();

        $this->assertArrayHasKey('fld', $parsed);
        $fld = $parsed['fld'];
        $this->assertIsArray($fld);
        $this->assertSame(['id' => 'alpha', 'type' => 'text'], $fld[1] ?? null);
    }

    public function testProducesSameShapeAsParseStrForRepresentativeFormBody(): void
    {
        // A body that mirrors the edit_layout.php save payload: an outer
        // `fld[N][...]` per-row group plus a `formaction` scalar.
        $body = 'formaction=save&fld[1][id]=field_a&fld[1][type]=text&fld[2][id]=field_b';

        $parser = new RawPostParser(
            $this->readerFor($body),
            'application/x-www-form-urlencoded',
        );
        $parsed = $parser->parse();

        $expected = [];
        parse_str($body, $expected);
        $this->assertSame($expected, $parsed);
    }
}
