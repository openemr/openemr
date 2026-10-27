<?php

/**
 * XPathVariableExpander isolated test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Schematron;

use DOMDocument;
use DOMNode;
use DOMXPath;
use OpenEMR\Services\Cda\Schematron\XPathVariableExpander;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class XPathVariableExpanderTest extends TestCase
{
    private const DOC = <<<'XML'
        <?xml version="1.0"?>
        <root>
          <item code="A1" qty="3"/>
          <item code="B2"/>
          <other code="A1"/>
        </root>
        XML;

    public function testExpressionWithoutVariablesIsUnchanged(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        self::assertSame(
            'count(../item) = 2',
            (new XPathVariableExpander())->expand('count(../item) = 2', ['s' => '@code'], $xpath, $node),
        );
    }

    public function testNodeSetVariableBecomesItsValueNotItsExpression(): void
    {
        // The point of the class: @code must be resolved against the rule context node,
        // not inlined into the predicate where it would compare each candidate to itself.
        [$xpath, $node] = self::contextAt('/root/item[1]');
        self::assertSame(
            "../other[@code=('A1')]",
            (new XPathVariableExpander())->expand('../other[@code=$c]', ['c' => '@code'], $xpath, $node),
        );
    }

    public function testInlinedExpressionWouldHaveBeenATautology(): void
    {
        // Guards the regression directly: with the value substituted, an element whose
        // code differs from the context node's must not satisfy the comparison.
        [$xpath, $node] = self::contextAt('/root/item[2]');
        $expanded = (new XPathVariableExpander())->expand('../other[@code=$c]', ['c' => '@code'], $xpath, $node);
        self::assertFalse($xpath->evaluate('boolean(' . $expanded . ')', $node));
    }

    public function testStringVariableIsQuoted(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        self::assertSame(
            "string-length(('A1')) = 2",
            (new XPathVariableExpander())->expand('string-length($s) = 2', ['s' => 'string(@code)'], $xpath, $node),
        );
    }

    public function testNumericVariableIsAPlainNumberWithoutExponent(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        self::assertSame(
            '(3) > 0',
            (new XPathVariableExpander())->expand('$n > 0', ['n' => 'number(@qty)'], $xpath, $node),
        );
    }

    public function testBooleanVariableBecomesABooleanFunction(): void
    {
        // Shaped like the QRDA $timeZoneExists let, whose value is a comparison.
        [$xpath, $node] = self::contextAt('/root/item[1]');
        self::assertSame(
            'true() or false()',
            (new XPathVariableExpander())->expand(
                '$a or $b',
                ['a' => 'string-length(@code) > 1', 'b' => 'string-length(@code) > 9'],
                $xpath,
                $node,
            ),
        );
    }

    public function testNonNumericArithmeticBecomesNan(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        $expanded = (new XPathVariableExpander())->expand('$n', ['n' => 'number(@code)'], $xpath, $node);
        self::assertSame("number('NaN')", $expanded);
        // NaN compares false against everything, which is what the NPI checksum relies on.
        self::assertFalse($xpath->evaluate('boolean(' . $expanded . ' = 0)', $node));
    }

    public function testEmptyNodeSetKeepsNodeSetComparisonSemantics(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        $expanded = (new XPathVariableExpander())->expand('$missing', ['missing' => '@nope'], $xpath, $node);
        // An empty node-set is false for both = and !=; an empty string would be true
        // for !=, which would flip every `$x != 'PCF'` branch in the QRDA III rules.
        self::assertFalse($xpath->evaluate("boolean($expanded = 'PCF')", $node));
        self::assertFalse($xpath->evaluate("boolean($expanded != 'PCF')", $node));
    }

    public function testChainedDefinitionsResolveTransitively(): void
    {
        // Mirrors the QRDA NPI checksum, where $n is defined in terms of $s.
        [$xpath, $node] = self::contextAt('/root/item[1]');
        self::assertSame(
            '(2) = 2',
            (new XPathVariableExpander())->expand(
                '$n = 2',
                ['s' => 'normalize-space(@code)', 'n' => 'string-length($s)'],
                $xpath,
                $node,
            ),
        );
    }

    public function testHyphenatedNameIsNotSplit(): void
    {
        // '-' is legal in an XML name, so $NPI must not match the head of $NPI-Count.
        [$xpath, $node] = self::contextAt('/root/item[1]');
        self::assertSame(
            '(1) = 1',
            (new XPathVariableExpander())->expand(
                '$NPI-Count = 1',
                ['NPI-Count' => 'count(../item[@qty])', 'NPI' => 'WRONG'],
                $xpath,
                $node,
            ),
        );
    }

    public function testMultiNodeVariableThrowsRatherThanGuessing(): void
    {
        [$xpath, $node] = self::contextAt('/root');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('selected more than one node');
        (new XPathVariableExpander())->expand('$all', ['all' => 'item/@code'], $xpath, $node);
    }

    public function testUndefinedVariableThrows(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Undefined schematron variable: $missing');
        (new XPathVariableExpander())->expand('$missing = 1', ['other' => '@code'], $xpath, $node);
    }

    public function testSelfReferentialDefinitionThrows(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        $this->expectException(RuntimeException::class);
        (new XPathVariableExpander())->expand('$a = 1', ['a' => 'concat($a, "x")'], $xpath, $node);
    }

    public function testMutuallyRecursiveDefinitionsThrow(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        $this->expectException(RuntimeException::class);
        (new XPathVariableExpander())->expand('$a = 1', ['a' => '$b', 'b' => '$a'], $xpath, $node);
    }

    public function testMalformedDefinitionThrows(): void
    {
        [$xpath, $node] = self::contextAt('/root/item[1]');
        $this->expectException(RuntimeException::class);
        (new XPathVariableExpander())->expand('$bad', ['bad' => 'this is not (xpath'], $xpath, $node);
    }

    /**
     * @return array{DOMXPath, DOMNode}
     */
    private static function contextAt(string $path): array
    {
        $doc = new DOMDocument();
        self::assertTrue($doc->loadXML(self::DOC));
        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query($path);
        self::assertNotFalse($nodes);
        $node = $nodes->item(0);
        self::assertInstanceOf(DOMNode::class, $node);
        return [$xpath, $node];
    }
}
