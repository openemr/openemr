<?php

namespace PubNub;


use Monolog\Logger;


abstract class PubNubCryptoCore{
    const IV_LENGTH = 16;

    /** @var  string */
    protected $cipherKey;

    /** @var  string */
    protected $initializationVector;

    /** @var  string */
    protected $useRandomIV;

    public function __construct($key, $useRandomIV, $initializationVector = "0123456789012345")
    {
        $this->cipherKey = $key;
        $this->useRandomIV = $useRandomIV;
        $this->initializationVector = $this->useRandomIV ? $this->randomIV() : substr($initializationVector, 0, 16);
    }

    /**
     * @param string | object $cipherText
     * @param Logger | null $logger
     * @return mixed
     */
    abstract function decrypt($cipherText, $logger = null);

    /**
     * @param mixed $plainText
     * @return mixed
     */
    abstract function encrypt($plainText);

    /**
     * @return mixed
     */
    public function randomIV() {
        if (function_exists("random_bytes")) {
            return random_bytes(static::IV_LENGTH);
        } else {
            // this is only used for initialization vector and is not necessary
            // to be cryptographically secure so fallback to openssl_random_pseudo_bytes
            // when random_bytes is not available
            return openssl_random_pseudo_bytes(static::IV_LENGTH);
        }
    }

    /**
     * @return string
     */
    public function getCipherKey()
    {
        return $this->cipherKey;
    }

    /**
     * @param string $cipherKey
     */
    public function setCipherKey($cipherKey)
    {
        $this->cipherKey = $cipherKey;
    }

    /**
     * @return string
     */
    public function getUseRandomIV()
    {
        return $this->useRandomIV;
    }

    /**
     * @param string $useRandomIV
     */
    public function setUseRandomIV($useRandomIV)
    {
        $this->useRandomIV = $useRandomIV;
    }

    public function pkcs5Pad($text, $blockSize) {
        $pad = $blockSize - (strlen($text) % $blockSize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public function unPadPKCS7($data, $blockSize) {
        $length = strlen($data);
        if ($length > 0) {
            $first = substr($data, -1);

            if (ord($first) <= $blockSize) {
                for ($i = $length - 2; $i > 0; $i--)
                    if (ord($data [$i] != $first))
                        break;

                return substr($data, 0, $i + 1);
            }
        }
        return $data;
    }

    public function isBlank($word) {
        if (($word == null) || ($word == false))
            return true;
        else
            return false;
    }

    protected function tryToJsonDecode($value) {
        $result = json_decode($value);

        if ($result === null) {
            return $value;
        } else {
            return $result;
        }
    }
}
