<?php

namespace PubNub;

use PubNub\Crypto\AesCbcCryptor;
use PubNub\Crypto\Cryptor;
use PubNub\Crypto\Header as CryptoHeader;
use PubNub\Crypto\LegacyCryptor;
use PubNub\Crypto\Payload as CryptoPayload;
use PubNub\Exceptions\PubNubCryptoException;
use PubNub\Exceptions\PubNubResponseParsingException;

class CryptoModule
{
    public const CRYPTOR_VERSION = 1;
    public const FALLBACK_CRYPTOR_ID = '0000';
    protected const SENTINEL = 'PNED';

    private $cryptorMap = [];
    private string $defaultCryptorId;

    public function __construct($cryptorMap, string $defaultCryptorId)
    {
        $this->cryptorMap = $cryptorMap;
        $this->defaultCryptorId = $defaultCryptorId;
    }

    public function registerCryptor(Cryptor $cryptor, ?string $cryptorId = null): self
    {
        if (is_null($cryptorId)) {
            $cryptorId = $cryptor::CRYPTOR_ID;
        }

        if (strlen($cryptorId) != 4) {
            throw new PubNubCryptoException('Malformed cryptor id');
        }

        if (key_exists($cryptorId, $this->cryptorMap)) {
            throw new PubNubCryptoException('Cryptor id already in use');
        }

        if (!$cryptor instanceof Cryptor) {
            throw new PubNubCryptoException('Invalid Cryptor instance');
        }

        $this->cryptorMap[$cryptorId] = $cryptor;

        return $this;
    }

    protected function stringify($data): string
    {
        if (is_string($data)) {
            return $data;
        } else {
            return json_encode($data);
        }
    }

    public function encrypt($data, ?string $cryptorId = null): string
    {
        if (($data) == '') {
            throw new PubNubResponseParsingException("Encryption error: message is empty");
        }
        $cryptorId = is_null($cryptorId) ? $this->defaultCryptorId : $cryptorId;
        $cryptor = $this->cryptorMap[$cryptorId];
        $text = $this->stringify($data);
        $cryptoPayload = $cryptor->encrypt($text);
        $header = $this->encodeHeader($cryptoPayload);
        return base64_encode($header . $cryptoPayload->getData());
    }

    public function decrypt(string | object $input): string | object
    {
        $input = $this->parseInput($input);
        $data = base64_decode($input);
        $header = $this->decodeHeader($data);

        if (!$this->cryptorMap[$header->getCryptorId()]) {
            throw new PubNubCryptoException('unknown cryptor error');
        }
        $payload = new CryptoPayload(
            substr($data, $header->getLength()),
            $header->getCryptorData(),
            $header->getCryptorId(),
        );

        return $this->cryptorMap[$header->getCryptorId()]->decrypt($payload);
    }

    public function encodeHeader(CryptoPayload $payload): string
    {
        if ($payload->getCryptorId() == self::FALLBACK_CRYPTOR_ID) {
            return '';
        }

        $version = chr(CryptoHeader::HEADER_VERSION);

        $crdLen = strlen($payload->getCryptorData());
        if ($crdLen > 65535) {
            throw new PubNubCryptoException('Cryptor data is too long');
        }

        if ($crdLen < 255) {
            $cryptorDataLength = chr($crdLen);
        } else {
            $hexlen = str_split(str_pad(dechex($crdLen), 4, 0, STR_PAD_LEFT), 2);
            $cryptorDataLength = chr(255) . chr(hexdec($hexlen[0])) . chr(hexdec($hexlen[1]));
        }

        return self::SENTINEL . $version . $payload->getCryptorId() . $cryptorDataLength . $payload->getCryptorData();
    }

    public function decodeHeader(string $header): CryptoHeader
    {
        if (strlen($header < 10) or substr($header, 0, 4) != self::SENTINEL) {
            return new CryptoHeader('', self::FALLBACK_CRYPTOR_ID, '', 0);
        }
        $sentinel = substr($header, 0, 4);
        $version = ord($header[4]);
        if ($version > CryptoHeader::HEADER_VERSION) {
            throw new PubNubCryptoException('unknown cryptor error');
        }
        $cryptorId = substr($header, 5, 4);
        $cryptorDataLength = ord($header[9]);
        if ($cryptorDataLength < 255) {
            $cryptorData = substr($header, 10, $cryptorDataLength);
            $headerLength = 10 + $cryptorDataLength;
        } else {
            $cryptorDataLength = ord($header[10]) * 256 + ord($header[11]);
            $cryptorData = substr($header, 12, $cryptorDataLength);
            $headerLength = 12 + $cryptorDataLength;
        }
        return new CryptoHeader($sentinel, $cryptorId, $cryptorData, $headerLength);
    }

    public static function legacyCryptor(string $cipherKey, bool $useRandomIV): self
    {
        return new self(
            [
                LegacyCryptor::CRYPTOR_ID => new LegacyCryptor($cipherKey, $useRandomIV),
                AesCbcCryptor::CRYPTOR_ID => new AesCbcCryptor($cipherKey),
            ],
            LegacyCryptor::CRYPTOR_ID
        );
    }

    public static function aesCbcCryptor(string $cipherKey, bool $useRandomIV): self
    {
        return new self(
            [
                LegacyCryptor::CRYPTOR_ID => new LegacyCryptor($cipherKey, $useRandomIV),
                AesCbcCryptor::CRYPTOR_ID => new AesCbcCryptor($cipherKey),
            ],
            aesCbcCryptor::CRYPTOR_ID
        );
    }

    // for backward compatibility
    public function getCipherKey()
    {
        return $this->cryptorMap[$this->defaultCryptorId]->getCipherKey();
    }

    public function parseInput(string | object $input): string
    {
        if (is_array($input)) {
            if (array_key_exists("pn_other", $input)) {
                $input = $input["pn_other"];
            } else {
                if (is_array($input)) {
                    throw new PubNubResponseParsingException("Decryption error: message is not a string");
                } else {
                    throw new PubNubResponseParsingException("Decryption error: pn_other object key missing");
                }
            }
        } elseif (!is_string($input)) {
            throw new PubNubResponseParsingException("Decryption error: message is not a string or object");
        }

        if (strlen($input) == '') {
            throw new PubNubResponseParsingException("Decryption error: message is empty");
        }
        return $input;
    }
}
