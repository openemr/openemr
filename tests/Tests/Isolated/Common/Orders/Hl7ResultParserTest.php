<?php

/**
 * Isolated tests for HL7 Result Parser classes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Orders;

use OpenEMR\Common\Orders\DefaultHl7ResultParser;
use OpenEMR\Common\Orders\Hl7ResultParseException;
use OpenEMR\Common\Orders\Hl7ResultParseResult;
use OpenEMR\Common\Orders\Hl7ResultParserInterface;
use PHPUnit\Framework\TestCase;

class Hl7ResultParserTest extends TestCase
{
    public function testDefaultHl7ResultParserImplementsInterface(): void
    {
        $this->assertTrue(
            is_subclass_of(DefaultHl7ResultParser::class, Hl7ResultParserInterface::class),
            'DefaultHl7ResultParser must implement Hl7ResultParserInterface'
        );
    }

    public function testHl7ResultParseResultConstructionWithDefaults(): void
    {
        $result = new Hl7ResultParseResult([]);

        $this->assertSame([], $result->messages);
        $this->assertFalse($result->fatal);
        $this->assertFalse($result->needsMatch);
    }

    public function testHl7ResultParseResultConstructionWithValues(): void
    {
        $messages = ['*Error occurred', '>Info message'];
        $result = new Hl7ResultParseResult($messages, true, true);

        $this->assertSame($messages, $result->messages);
        $this->assertTrue($result->fatal);
        $this->assertTrue($result->needsMatch);
    }

    public function testFromLegacyArrayWithAllKeys(): void
    {
        $legacy = [
            'mssgs' => ['*Fatal error', '>Processing complete'],
            'fatal' => true,
            'needmatch' => true,
        ];

        $result = Hl7ResultParseResult::fromLegacyArray($legacy);

        $this->assertSame(['*Fatal error', '>Processing complete'], $result->messages);
        $this->assertTrue($result->fatal);
        $this->assertTrue($result->needsMatch);
    }

    public function testFromLegacyArrayWithMinimalKeys(): void
    {
        $legacy = [
            'mssgs' => ['>Result received'],
        ];

        $result = Hl7ResultParseResult::fromLegacyArray($legacy);

        $this->assertSame(['>Result received'], $result->messages);
        $this->assertFalse($result->fatal);
        $this->assertFalse($result->needsMatch);
    }

    public function testFromLegacyArrayWithEmptyArray(): void
    {
        $result = Hl7ResultParseResult::fromLegacyArray([]);

        $this->assertSame([], $result->messages);
        $this->assertFalse($result->fatal);
        $this->assertFalse($result->needsMatch);
    }

    public function testToLegacyArrayRoundTrip(): void
    {
        $legacy = [
            'mssgs' => ['*Error one', '>Info two'],
            'fatal' => true,
            'needmatch' => true,
        ];

        $result = Hl7ResultParseResult::fromLegacyArray($legacy);
        $output = $result->toLegacyArray();

        $this->assertSame($legacy['mssgs'], $output['mssgs']);
        $this->assertTrue($output['fatal']);
        $this->assertTrue($output['needmatch']);
    }

    public function testToLegacyArrayOmitsFalseFlags(): void
    {
        $result = new Hl7ResultParseResult(['>Info only']);
        $output = $result->toLegacyArray();

        $this->assertSame(['>Info only'], $output['mssgs']);
        $this->assertArrayNotHasKey('fatal', $output);
        $this->assertArrayNotHasKey('needmatch', $output);
    }

    public function testHl7ResultParseExceptionExtendsRuntimeException(): void
    {
        $exception = new Hl7ResultParseException('Test error');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('Test error', $exception->getMessage());
    }
}
