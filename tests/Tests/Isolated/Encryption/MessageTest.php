<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption;

use BadMethodCallException;
use OpenEMR\Encryption\{
    Ciphertext,
    KeyId,
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
        1 => '001',
        2 => '002',
        3 => '003',
        4 => '004',
        5 => '005',
        6 => '006',
        7 => '007',
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

    public function testParseRoundtripImplicitFormat(): void
    {
        $data = $this->fixtures->getCiphertext(7);
        $message = Message::parse($data);
        assert($message->format === MessageFormat::ImplicitKey);
        $reencoded = $message->encode();
        self::assertSame($data, $reencoded);
    }
    #[DataProvider('previousFormatsProvider')]
    public function testParsingPreviousFormats(int $version, string $expectedKeyId): void
    {
        $data = $this->fixtures->getCiphertext($version);
        $message = Message::parse($data);

        self::assertSame(MessageFormat::ImplicitKey, $message->format);
        self::assertSame($expectedKeyId, $message->keyId->id);
        self::assertSame($data, $message->encode(), 'Roundtrip encoding failed');
    }

    public function testConstructWithImplicitKeyFormat(): void
    {
        $keyId = new KeyId('005');
        $ciphertext = new Ciphertext('test data');
        $message = new Message($keyId, $ciphertext, MessageFormat::ImplicitKey);

        self::assertSame(MessageFormat::ImplicitKey, $message->format);
        self::assertStringStartsWith('005', $message->encode());
    }

    public function testConstructWithInvalidImplicitKey(): void
    {
        $keyId = new KeyId('not-a-number');
        $ciphertext = new Ciphertext('test data');
        $this->expectException(BadMethodCallException::class);
        $message = new Message($keyId, $ciphertext, MessageFormat::ImplicitKey);
    }


    public function testConstructParseRoundtripImplicitKey(): void
    {
        $keyId = new KeyId('007');
        $ciphertext = new Ciphertext('some encrypted data');
        $message = new Message($keyId, $ciphertext, MessageFormat::ImplicitKey);
        $encoded = $message->encode();
        $parsed = Message::parse($encoded);
        self::assertSame($keyId->id, $parsed->keyId->id, 'Key mismatch');
        self::assertSame($ciphertext->value, $parsed->ciphertext->value, 'Ciphertext mismatch');
    }

    public function testParseThrowsOnTooShortMessage(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Message is missing expected prefix');
        Message::parse('00');
    }

    public function testParseThrowsOnEmptyMessage(): void
    {
        // This test may be removed if plaintext support is added.
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
        // Version 999 is an invalid prefix.
        Message::parse('999' . base64_encode('test'));
    }
}
