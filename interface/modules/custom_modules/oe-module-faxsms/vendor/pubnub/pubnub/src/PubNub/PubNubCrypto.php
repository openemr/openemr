<?php

namespace PubNub;


use Monolog\Logger;
use PubNub\Exceptions\PubNubResponseParsingException;

class PubNubCrypto extends PubNubCryptoCore {
    public function encrypt($plainText) {
        $shaCipherKey = hash("sha256", $this->cipherKey);
        $paddedCipherKey = substr($shaCipherKey, 0, 32);

        $encrypted = openssl_encrypt($plainText, 'aes-256-cbc', $paddedCipherKey, OPENSSL_RAW_DATA,
            $this->initializationVector);

        $encode = '';
        
        if ($this->useRandomIV) {
            $encode = base64_encode($this->initializationVector . $encrypted);
        } else {
            $encode = base64_encode($encrypted);
        }

        return $encode;
    }

    public function decrypt($cipherText, $logger = null) {
        $logError = function ($message) use ($logger) {
            if ($logger !== null && $logger instanceof Logger) {
                $logger->error($message);
            }
        };

        if (is_array($cipherText)) {
            if (array_key_exists("pn_other", $cipherText)) {
                $cipherText = $cipherText["pn_other"];
            } else {
                if (is_array($cipherText)) {
                    $logError("Decryption error: message is not a string or object");
                    throw new PubNubResponseParsingException("Decryption error: message is not a string");
                } else {
                    $logError("Decryption error: pn_other object key missing: " . $cipherText);
                    throw new PubNubResponseParsingException("Decryption error: pn_other object key missing");
                }
            }
        } else if (!is_string($cipherText)) {
            $logError("Decryption error: message is not a string: " . $cipherText);
            throw new PubNubResponseParsingException("Decryption error: message is not a string or object");
        }

        if (strlen($cipherText) === 0){
            $logError("Decryption error: message is empty");
            throw new PubNubResponseParsingException("Decryption error: message is empty");
        }

        $shaCipherKey = hash("sha256", $this->cipherKey);
        $paddedCipherKey = substr($shaCipherKey, 0, 32);

        $decoded = base64_decode($cipherText);

        $data = '';
        $initializationVector = '';

        if ($this->useRandomIV) {
            $initializationVector = substr($decoded, 0, 16);
            $data = substr($decoded, 16);
        } else {
            $initializationVector = $this->initializationVector;
            $data = $decoded;
        }

        $decrypted = openssl_decrypt($data, 'aes-256-cbc', $paddedCipherKey, OPENSSL_RAW_DATA,
            $initializationVector);

        if ($decrypted === false) {
            $logError("Decryption error: " . openssl_error_string());
            throw new PubNubResponseParsingException("Decryption error: " . openssl_error_string());
        }

        $unPadded = $this->unPadPKCS7($decrypted, 16);

        $result = json_decode($unPadded);

        if ($result === null) {
            return $unPadded;
        } else {
            return $result;
        }
    }
}
