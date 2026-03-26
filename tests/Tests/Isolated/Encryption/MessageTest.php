<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption;

use OpenEMR\Encryption\{
    Ciphertext,
    Keys\Id,
    Message,
    MessageFormat,
};
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
#[Small]
class MessageTest extends TestCase
{
    public function testParseRoundtrip(): void
    {
        $message = Message::parse($data);
        $reencoded = $message->encode();
        self::assertSame($data, $reencoded);
    }

    public function testConstructRoundtrip(): void
    {
        $keyId = new Id('test-key');
        $ciphertext = new Ciphertext('some encrypted data');
        $message = new Message($keyId, $ciphertext);
        $encoded = $message->encode();
        $parsed = Message::parse($encoded);
        self::assertSame($keyId->id, $parsed->keyId->id, 'Key mismatch');

    }

    public function testParsingPreviousFormats(string $data): void
    {

    }
}
