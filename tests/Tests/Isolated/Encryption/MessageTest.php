<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption;

use OpenEMR\Encryption\{
    Ciphertext,
    Keys\Id,
    Message,
    MessageFormat,
};
use OpenEMR\Tests\Fixtures\CryptoFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[Small]
class MessageTest extends TestCase
{
    private CryptoFixtureManager $fixtures;

    /**
     * Expected key IDs for each message format version.
     */
    private const EXPECTED_KEY_IDS = [
        1 => 'one',
        2 => 'two',
        3 => 'two', // v3 shares v2 keys
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
    ];

    protected function setUp(): void
    {
        $this->fixtures = new CryptoFixtureManager('/dev/null');
    }

    /**
     * @return iterable<string, array{version: int, expectedKeyId: string}>
     */
    public static function previousFormatsProvider(): iterable
    {
        foreach (self::EXPECTED_KEY_IDS as $version => $keyId) {
            yield "version $version" => [
                'version' => $version,
                'expectedKeyId' => $keyId,
            ];
        }
    }

    public function testParseRoundtrip(): void
    {
        $data = $this->fixtures->getCiphertext(7);
        $message = Message::parse($data);
        $reencoded = $message->encode();
        self::assertSame($data, $reencoded);
    }

    public function testConstructRoundtrip(): void
    {
        // IMPORTANT: once we support message format v8, this test should start
        // failing and get updated so the keys align again.
        $keyId = new Id('some-key-id');
        $ciphertext = new Ciphertext('some encrypted data');
        $message = new Message($keyId, $ciphertext);
        $encoded = $message->encode();
        $parsed = Message::parse($encoded);
        self::assertSame('seven', $parsed->keyId->id, 'Key mismatch');
        self::assertSame($ciphertext->wrapped, $parsed->ciphertext->wrapped, 'Ciphertext mismatch');
    }

    #[DataProvider('previousFormatsProvider')]
    public function testParsingPreviousFormats(int $version, string $expectedKeyId): void
    {
        $data = $this->fixtures->getCiphertext($version);
        $message = Message::parse($data);

        self::assertSame($version, $message->format->value);
        self::assertSame($expectedKeyId, $message->keyId->id);
        self::assertSame($data, $message->encode(), 'Roundtrip encoding failed');
    }

    public function testConstructWithExplicitFormat(): void
    {
        $keyId = new Id('four');
        $ciphertext = new Ciphertext('test data');
        $message = new Message($keyId, $ciphertext, MessageFormat::v4);

        self::assertSame(MessageFormat::v4, $message->format);
        self::assertStringStartsWith('004', $message->encode());
    }

    public function testParseThrowsOnTooShortMessage(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Message is missing expected prefix');
        Message::parse('00');
    }

    public function testParseThrowsOnEmptyMessage(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Message is missing expected prefix');
        Message::parse('');
    }

    public function testParseThrowsOnInvalidBase64(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not parse ciphertext');
        Message::parse('007!!!invalid-base64!!!');
    }

    public function testParseThrowsOnInvalidVersion(): void
    {
        $this->expectException(\ValueError::class);
        // Version 999 doesn't exist in MessageFormat enum
        Message::parse('999' . base64_encode('test'));
    }

    public function testEncodeProducesCorrectPrefix(): void
    {
        $keyId = new Id('test');
        $ciphertext = new Ciphertext('data');

        foreach (MessageFormat::cases() as $format) {
            $message = new Message($keyId, $ciphertext, $format);
            $encoded = $message->encode();
            $expectedPrefix = sprintf('%03d', $format->value);
            self::assertStringStartsWith($expectedPrefix, $encoded, "Format {$format->name} should produce prefix $expectedPrefix");
        }
    }
}
