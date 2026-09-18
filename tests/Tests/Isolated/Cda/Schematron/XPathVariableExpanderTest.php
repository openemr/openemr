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

use OpenEMR\Services\Cda\Schematron\XPathVariableExpander;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class XPathVariableExpanderTest extends TestCase
{
    public function testTestWithoutVariablesIsUnchanged(): void
    {
        $expander = new XPathVariableExpander();
        self::assertSame('count(cda:id) > 0', $expander->expand('count(cda:id) > 0', ['s' => '@extension']));
    }

    public function testSingleVariableIsInlinedParenthesized(): void
    {
        $expander = new XPathVariableExpander();
        self::assertSame(
            'string-length((normalize-space(@extension))) = 10',
            $expander->expand('string-length($s) = 10', ['s' => 'normalize-space(@extension)']),
        );
    }

    public function testChainedVariablesResolveTransitively(): void
    {
        // Mirrors the QRDA NPI checksum, where $n is defined in terms of $s.
        $expander = new XPathVariableExpander();
        self::assertSame(
            '((string-length((normalize-space(@extension))))) > 0',
            $expander->expand('($n) > 0', ['s' => 'normalize-space(@extension)', 'n' => 'string-length($s)']),
        );
    }

    public function testEveryOccurrenceIsReplaced(): void
    {
        $expander = new XPathVariableExpander();
        self::assertSame(
            '(@a) or (@a)',
            $expander->expand('$v or $v', ['v' => '@a']),
        );
    }

    public function testHyphenatedNameIsNotSplit(): void
    {
        // '-' is legal in an XML name, so $NPI must not match the head of $NPI-Count.
        $expander = new XPathVariableExpander();
        self::assertSame(
            '(count(cda:id)) = 1',
            $expander->expand('$NPI-Count = 1', ['NPI-Count' => 'count(cda:id)', 'NPI' => 'WRONG']),
        );
    }

    public function testUndefinedVariableThrows(): void
    {
        $expander = new XPathVariableExpander();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Undefined schematron variable: $missing');
        $expander->expand('$missing = 1', ['other' => '@a']);
    }

    public function testSelfReferentialDefinitionThrows(): void
    {
        $expander = new XPathVariableExpander();
        $this->expectException(RuntimeException::class);
        $expander->expand('$a = 1', ['a' => 'concat($a, "x")']);
    }

    public function testMutuallyRecursiveDefinitionsThrow(): void
    {
        $expander = new XPathVariableExpander();
        $this->expectException(RuntimeException::class);
        $expander->expand('$a = 1', ['a' => '$b', 'b' => '$a']);
    }
}
