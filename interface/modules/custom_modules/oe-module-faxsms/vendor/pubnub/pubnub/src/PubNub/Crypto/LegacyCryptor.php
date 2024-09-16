<?php

namespace PubNub\Crypto;

use PubNub\Crypto\Cryptor;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Crypto\PaddingTrait;

class LegacyCryptor extends Cryptor
{
    use PaddingTrait;

    public const CRYPTOR_ID = '0000';
    public const IV_LENGTH = 16;
    public const BLOCK_SIZE = 16;
    public const CIPHER_ALGO = 'aes-256-cbc';
    protected const STATIC_IV = '0123456789012345';

    protected string $cipherKey;
    protected bool $useRandomIV;

    public function __construct(string $key, bool $useRandomIV)
    {
        $this->cipherKey = $key;
        $this->useRandomIV = $useRandomIV;
    }

    public function getIV(): string
    {
        if (!$this->useRandomIV) {
            return self::STATIC_IV;
        }
        return random_bytes(static::IV_LENGTH);
    }

    public function getCipherKey(): string
    {
        return $this->cipherKey;
    }

    public function encrypt(string $text, ?string $cipherKey = null): Payload
    {
        $iv = $this->getIV();
        $shaCipherKey = substr(hash("sha256", $this->cipherKey), 0, 32);
        $padded = $this->pad($text, self::BLOCK_SIZE);
        $encrypted = openssl_encrypt($text, self::CIPHER_ALGO, $shaCipherKey, OPENSSL_RAW_DATA, $iv);
        if ($this->useRandomIV) {
            $encryptedWithIV = $iv . $encrypted;
        } else {
            $encryptedWithIV = $encrypted;
        }
        return new Payload($encryptedWithIV, '', self::CRYPTOR_ID);
    }

    public function decrypt(Payload $payload, ?string $cipherKey = null)
    {
        $text = $payload->getData();
        if (strlen($text) === 0) {
            throw new PubNubResponseParsingException("Decryption error: message is empty");
        }

        if (is_array($text)) {
            if (array_key_exists("pn_other", $text)) {
                $text = $text["pn_other"];
            } else {
                if (is_array($text)) {
                    throw new PubNubResponseParsingException("Decryption error: message is not a string");
                } else {
                    throw new PubNubResponseParsingException("Decryption error: pn_other object key missing");
                }
            }
        } elseif (!is_string($text)) {
            throw new PubNubResponseParsingException("Decryption error: message is not a string or object");
        }

        $shaCipherKey = substr(hash("sha256", $this->cipherKey), 0, 32);

        if ($this->useRandomIV) {
            $iv = substr($text, 0, 16);
            $data = substr($text, 16);
        } else {
            $iv = self::STATIC_IV;
            $data = $text;
        }
        $decrypted = openssl_decrypt($data, 'aes-256-cbc', $shaCipherKey, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new PubNubResponseParsingException("Decryption error: " . openssl_error_string());
        }

        $unPadded = $this->depad($decrypted, self::BLOCK_SIZE);

        $result = json_decode($unPadded);

        if ($result === null) {
            return $unPadded;
        } else {
            return $result;
        }
    }
}
