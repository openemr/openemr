<?php

/**
 * Tests for PasswordBasedCrypto
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Crypto;

use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\PasswordBasedCrypto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PasswordBasedCryptoTest extends TestCase
{
    /**
     * Test vectors generated from CryptoGen to ensure backwards compatibility.
     * PasswordBasedCrypto MUST be able to decrypt these.
     */
    public static function legacyVectorProvider(): iterable
    {
        yield 'simple text' => [
            'plaintext' => 'hello world',
            'password' => 'secret123',
            'ciphertext' => '007NIAbofhxSOB+JR4YFRxwt4JkozzRAVk161fYgfb5FE1BXf8taMD9jCx00D//3KjDwXMHu/cbxC2Z/yKjhm3qTnD++mVti3+4XHkIG/+F3hnBF47Zfoxcri+VzSeOiq+WJGQP8pECfjAHeijxB8RRgw==',
        ];

        yield 'empty plaintext' => [
            'plaintext' => '',
            'password' => 'emptytest',
            'ciphertext' => '007ZmIT8cMTnlsF8Eu+45q6SaZ9yHeKj7wzexzHVzYCzzo8nASGQb6o6pnSL+abpbC4kZzPjTDVpT8IpegsHZ38s5RbGc2S9RjjOTdGeciMBYFC9fYLo1nfGRoqpTsfUay96YX9KLk5g45EB432OVFOGA==',
        ];

        yield 'unicode characters' => [
            'plaintext' => 'Special chars: áéíóú',
            'password' => 'unicode',
            'ciphertext' => '0077Me0gIuRCCUhJmteSZPRLoUimRUu5IyAPIYBBfY3JC373Dxh+ct0D1ATZXtpWY3yXTI47zcffTeYYbuur+0eVyWLXHTJHBiMKHm/G9CMSLbnMVHH19BXrgmxS68iVyGaIZ/CSZO41SAUMXHEWmSL5UfSYwMK2jV++aMogbyd0KI=',
        ];

        yield 'emoji' => [
            'plaintext' => 'Emoji test: 🎉🔐💻',
            'password' => 'emojipass',
            'ciphertext' => '007QQfqbSiM9ZWwRulwxKUP3T8kS72LaTdYoBZ4jPHQBdNLKFw5qWmP3P6gMIMbZmvyYYTAtnzG03FUejB6WGso+Bdb8kcPYeU1icUoteYCeIE43ZNXfMGsN+Sav0gJaIYbM6NTfftcjmW2ox3onnVATy7XlPS1Z9yGkRXDDFZ1Hf4=',
        ];

        yield 'long text (1000 chars)' => [
            'plaintext' => str_repeat('x', 1000),
            'password' => 'longtext',
            'ciphertext' => '007UQzTf3BJYGcD0b/njjLQbtd2sHF7fQ07WYBqAA9YQoCjaYS+Jeg0Bhvq49ToI1Apm3GqsY1QUIu2qFlBgfORSX3m8NTf7xzufrfoyYv1cvKDfIpCtig/VNS2xw5jRmp0yQKXj+fos8BMHB/fzSvOAe/YlXU5ypyowWU169W6KA8HtzluL6a7fB6OkllRxjQ2HOnsb8BV6Tiy9bFQKH3B6a7H1vs38Ngs+e02BWHgXRNdGfptPNmePZ5VoQJrTk7C9RI6DD6/r9aIkaS9yasHlls57kYeg2UrKR6ejo3CDsdoSqLQ5irOUoj1h3XJE33L4K+/Y6IN0hXmEgwYFsGXFT/2Xb1tvEawmFT/0/AeXwB9tX54EzBweQaBZOxlrvWWCnngd7LsKpnMLU4I9UWOX62+hhU7dcNBNPwJcuFGtUIfetstNOf/AdiaB4mxAGMKmml9/+2di7Zt3Zbs43AphzU4iMK2oYBaRAkUZQ0fE0KklR/E+6tNlYqUfj3/uUwoRW+l/FB3vYml+zK8T9hn36eR8yGb3KOqSkJ9Fz98G/h98yZ97Dv6AkoyxZFDJHDpR84Drf8/WgRP86A1z7lIxbGLaPJwPTCKqpUrea2mVVjskjeXqlrk7SMEfdhVpXH7Iq+D20cfyFDyacsVAhPSVkszWlqqmT6Fzm3gL4e8T1372p4G06C7kiCpgktpmtkn6oE6flp9o0lJlZusqj00aheLZ1CG3UX9NubiJbM/YNMN8P9ZtoYfKGBCStPlLQBsdt+nRqIpEmX/H1pyizzUfs+H4XWi0gA4fw3/imVAMLKj9MPg07Eetz5vNzcW90R2KMrBh/PE7yH+nKK+fylYU2ti1EgX7egMQrD5oDl6wWf76h4YM4uRHebRDwFulprOUn1iThlTMD+5yeBrAGV0Gj/PMbjl4WUMRxL/RvNw2ue7yJcxM3QyDunW1PvptsGmKzFZZODOpwGd2K7q4y2Rze4P9c6M0oVCqjEA0RkPX1WlQPMnEK89Nh+gqKrJHsK1bX2icieq7NCL/g2IVg23WQxcI7+a8Y8C9X71MjPkOAOn7xEXNjTCbWDXqIzGQuUjFmoEkc9XCA3p+Oiz0Hjl8NwzOd/z4QvgLvqiJURL5nXCu/cxyxuiLc8bG+Iy9hFLiCvZcNXAWSvueTP9dxX2kemKkPkrbdyR6Im/6Fwlnc4KhQ9smQzkQB6+oaOqZOWgosG1F1uzpGNnPMLDypNdm2DhzDfzQF4w1nMFzFi5hAY5XqMp7ilKbfkh67BUDg04OxRCNS5E0rquemApeu2gNxB2lkg/RUIx5HVjIou2fm/v2H55yJb2A6/K/dhuqodqd8egIVaqPIK8R+yunBbOKK02jmwEt1OdQTLhF7dw2e5eZR6DgTJ4CsyugNeTVRt/BHdhpXzPR9ME8RaSS3kSi0OGe6thjdm2c7FWIy5ArGqSlK0ooDss/VOf/Pzdw7bB',
        ];
    }

    #[DataProvider('legacyVectorProvider')]
    public function testDecryptsLegacyCryptoGenData(string $plaintext, string $password, string $ciphertext): void
    {
        $crypto = new PasswordBasedCrypto();
        $decrypted = $crypto->decrypt($ciphertext, $password);
        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundtrip(): void
    {
        $crypto = new PasswordBasedCrypto();
        $plaintext = 'This is a test message';
        $password = 'mypassword';

        $encrypted = $crypto->encrypt($plaintext, $password);
        $decrypted = $crypto->decrypt($encrypted, $password);

        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundtripEmptyString(): void
    {
        $crypto = new PasswordBasedCrypto();
        $plaintext = '';
        $password = 'mypassword';

        $encrypted = $crypto->encrypt($plaintext, $password);
        $decrypted = $crypto->decrypt($encrypted, $password);

        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundtripUnicode(): void
    {
        $crypto = new PasswordBasedCrypto();
        $plaintext = 'Unicode: 日本語 中文 한국어 🎉';
        $password = 'unicodepass';

        $encrypted = $crypto->encrypt($plaintext, $password);
        $decrypted = $crypto->decrypt($encrypted, $password);

        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptProducesVersionPrefix(): void
    {
        $crypto = new PasswordBasedCrypto();
        $encrypted = $crypto->encrypt('test', 'password');

        self::assertStringStartsWith('007', $encrypted);
    }

    public function testEncryptProducesDifferentCiphertextEachTime(): void
    {
        $crypto = new PasswordBasedCrypto();
        $plaintext = 'same plaintext';
        $password = 'samepassword';

        $encrypted1 = $crypto->encrypt($plaintext, $password);
        $encrypted2 = $crypto->encrypt($plaintext, $password);

        // Different due to random salt and IV
        self::assertNotSame($encrypted1, $encrypted2);

        // But both decrypt to the same value
        self::assertSame($plaintext, $crypto->decrypt($encrypted1, $password));
        self::assertSame($plaintext, $crypto->decrypt($encrypted2, $password));
    }

    public function testDecryptWithWrongPasswordThrows(): void
    {
        $crypto = new PasswordBasedCrypto();
        $encrypted = $crypto->encrypt('secret data', 'correctpassword');

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC');

        $crypto->decrypt($encrypted, 'wrongpassword');
    }

    public function testDecryptWithCorruptedCiphertextThrows(): void
    {
        $crypto = new PasswordBasedCrypto();
        $encrypted = $crypto->encrypt('test', 'password');

        // Corrupt the base64 payload (after the 007 prefix)
        $corrupted = '007' . substr($encrypted, 3, 10) . 'CORRUPTED' . substr($encrypted, 20);

        $this->expectException(CryptoGenException::class);

        $crypto->decrypt($corrupted, 'password');
    }

    public function testDecryptWithInvalidBase64Throws(): void
    {
        $crypto = new PasswordBasedCrypto();

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('base64');

        $crypto->decrypt('007!!!invalid-base64!!!', 'password');
    }

    public function testDecryptWithTruncatedDataThrows(): void
    {
        $crypto = new PasswordBasedCrypto();
        $encrypted = $crypto->encrypt('test', 'password');

        // Truncate to just the prefix + partial data
        $truncated = substr($encrypted, 0, 20);

        $this->expectException(CryptoGenException::class);

        $crypto->decrypt($truncated, 'password');
    }
}
