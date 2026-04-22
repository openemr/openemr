<?php

/**
 * Tests for PasswordBasedCrypto
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Crypto;

use OpenEMR\Common\Crypto\{
    CryptoGenException,
    KeyVersion,
    PasswordBasedCrypto,
};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PasswordBasedCryptoTest extends TestCase
{
    private KeyVersion $version;

    public function setUp(): void
    {
        $this->version = KeyVersion::SEVEN;
    }

    /**
     * Test vectors for version 1: sha256(password) hex, no HMAC, format: iv + data
     *
     * @return iterable<string, array{plaintext: string, password: string, ciphertext: string}>
     */
    public static function v1VectorProvider(): iterable
    {
        yield 'v1 simple text' => [
            'plaintext' => 'hello world',
            'password' => 'secret123',
            'ciphertext' => '001cHAtdGAycv+STmWDKWSLqrUGHb16E9RH/kNyJVPQ4L8=',
        ];

        yield 'v1 empty' => [
            'plaintext' => '',
            'password' => 'emptytest',
            'ciphertext' => '00120mE2nSfdaFR2DaCr4OfntSmM8jDlihSy4J6Hu4jGQg=',
        ];

        yield 'v1 unicode' => [
            'plaintext' => 'Special chars: áéíóú',
            'password' => 'unicode',
            'ciphertext' => '001/Nf47ieA3sey/zi9IAJPZ+UL75wb1sa5SUv1GwoKqtEGouOBWatuIpYWSererbX3',
        ];
    }

    /**
     * Test vectors for versions 2-3: sha256(password) binary, HMAC-SHA256, format: hmac(32) + iv + data
     *
     * @return iterable<string, array{plaintext: string, password: string, ciphertext: string}>
     */
    public static function v2v3VectorProvider(): iterable
    {
        yield 'v2 simple text' => [
            'plaintext' => 'hello world',
            'password' => 'secret123',
            'ciphertext' => '0020z1Rc9l7MCeJPxvORhiVzVMrw8/YG1c0kaDncbHcHNsMvFONvkRZyu1/HfLMozThf/AlheUzyF655ndAZfiDqw==',
        ];

        yield 'v2 empty' => [
            'plaintext' => '',
            'password' => 'emptytest',
            'ciphertext' => '002w3w7Lm/GYxo2DphTSHNTIHhiybOC6DPZWUsO9OIM54hncZL/rOtLFsCkIBenwPPgOo4MNWPohFK9z9SuVc2OKw==',
        ];

        yield 'v2 unicode' => [
            'plaintext' => 'Special chars: áéíóú',
            'password' => 'unicode',
            'ciphertext' => '002grPsnzm9nEsiI8o0zitCiJgQDxYEs8q3DVIun0AVOuQB9vcxE/wZtjXeIcmWpF3ViV9rn66Wx2s0Wyx0ZKtgV+wvWQR7HEDb2Hh+NiXz8VQ=',
        ];

        yield 'v3 simple text' => [
            'plaintext' => 'hello world',
            'password' => 'secret123',
            'ciphertext' => '003tndM1BoFqJ34IXVbV+6dzGCVJ+uQIy3fujZjCMIEfG5rMLV8zIBfDL+fta4Vm8KPDikCs056xSnO4u5XqSUVmQ==',
        ];

        yield 'v3 empty' => [
            'plaintext' => '',
            'password' => 'emptytest',
            'ciphertext' => '003qOTV2JkQTYx7Aa6AgOtYsnfckv9ECikU90vejOGLRIkTcdOZXyk3KLCthTppJ3rp79v2+kc9qjr/S93KJZWEwQ==',
        ];

        yield 'v3 unicode' => [
            'plaintext' => 'Special chars: áéíóú',
            'password' => 'unicode',
            'ciphertext' => '003TslQxnlVc+fk+ww1Wplo/v7XKwpi62I+s8uusnXbbY66EZWTlnE2c3JPIk+LTqF2eYkSHZg48VzdYXbnyeBZrpNRyju7Ro/qVFi7g4DHxiw=',
        ];
    }

    /**
     * Test vectors generated from CryptoGen v4-7 to ensure backwards compatibility.
     * PasswordBasedCrypto MUST be able to decrypt these.
     *
     * @return iterable<string, array{plaintext: string, password: string, ciphertext: string}>
     */
    public static function modernVectorProvider(): iterable
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

    #[DataProvider('v1VectorProvider')]
    public function testDecryptsV1Data(string $plaintext, string $password, string $ciphertext): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $decrypted = $crypto->decrypt($ciphertext, $password);
        self::assertSame($plaintext, $decrypted);
    }

    #[DataProvider('v2v3VectorProvider')]
    public function testDecryptsV2V3Data(string $plaintext, string $password, string $ciphertext): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $decrypted = $crypto->decrypt($ciphertext, $password);
        self::assertSame($plaintext, $decrypted);
    }

    #[DataProvider('modernVectorProvider')]
    public function testDecryptsModernData(string $plaintext, string $password, string $ciphertext): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $decrypted = $crypto->decrypt($ciphertext, $password);
        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundtrip(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $plaintext = 'This is a test message';
        $password = 'mypassword';

        $encrypted = $crypto->encrypt($plaintext, $password);
        $decrypted = $crypto->decrypt($encrypted, $password);

        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundtripEmptyString(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $plaintext = '';
        $password = 'mypassword';

        $encrypted = $crypto->encrypt($plaintext, $password);
        $decrypted = $crypto->decrypt($encrypted, $password);

        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundtripUnicode(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $plaintext = 'Unicode: 日本語 中文 한국어 🎉';
        $password = 'unicodepass';

        $encrypted = $crypto->encrypt($plaintext, $password);
        $decrypted = $crypto->decrypt($encrypted, $password);

        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptProducesVersionPrefix(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $encrypted = $crypto->encrypt('test', 'password');

        self::assertStringStartsWith('007', $encrypted);
    }

    public function testEncryptProducesDifferentCiphertextEachTime(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
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

    public function testDecryptV1WithWrongPasswordThrows(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        // V1 has no HMAC, so wrong password causes decryption failure
        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('Could not decrypt');

        $crypto->decrypt('001cHAtdGAycv+STmWDKWSLqrUGHb16E9RH/kNyJVPQ4L8=', 'wrongpassword');
    }

    public function testDecryptV2V3WithWrongPasswordThrows(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC');

        $crypto->decrypt('0020z1Rc9l7MCeJPxvORhiVzVMrw8/YG1c0kaDncbHcHNsMvFONvkRZyu1/HfLMozThf/AlheUzyF655ndAZfiDqw==', 'wrongpassword');
    }

    public function testDecryptModernWithWrongPasswordThrows(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $encrypted = $crypto->encrypt('secret data', 'correctpassword');

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC');

        $crypto->decrypt($encrypted, 'wrongpassword');
    }

    public function testDecryptWithCorruptedCiphertextThrows(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);
        $encrypted = $crypto->encrypt('test', 'password');

        // Corrupt the base64 payload (after the 007 prefix)
        $corrupted = '007' . substr($encrypted, 3, 10) . 'CORRUPTED' . substr($encrypted, 20);

        $this->expectException(CryptoGenException::class);

        $crypto->decrypt($corrupted, 'password');
    }

    public function testDecryptWithInvalidBase64Throws(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('base64');

        $crypto->decrypt('007!!!invalid-base64!!!', 'password');
    }

    public function testDecryptWithTruncatedDataThrows(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);

        // Create valid base64 with only 50 bytes (well under 112 minimum)
        $shortPayload = str_repeat('A', 50);
        $ciphertext = '007' . base64_encode($shortPayload);

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('too short');

        $crypto->decrypt($ciphertext, 'password');
    }

    public function testDecryptWithPayloadJustUnderMinimumThrows(): void
    {
        $crypto = new PasswordBasedCrypto($this->version);

        // MIN_PAYLOAD_LENGTH = 32 (salt) + 48 (hmac) + 16 (iv) + 16 (min ciphertext) = 112
        // Create payload of 111 bytes (just under minimum)
        $shortPayload = str_repeat("\x00", 111);
        $ciphertext = '007' . base64_encode($shortPayload);

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('too short');

        $crypto->decrypt($ciphertext, 'password');
    }
}
