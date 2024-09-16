<?php

namespace PubNub;

// https://paragonie.com/blog/2015/05/if-you-re-typing-word-mcrypt-into-your-code-you-re-doing-it-wrong

class PubNubCryptoLegacy extends PubNubCryptoCore {
    public function encrypt($plainText) {
        $shaCipherKey = hash("sha256", $this->cipherKey);
        $paddedCipherKey = substr($shaCipherKey, 0, 32);

        $padded_plain_text = $this->pkcs5Pad($plainText, 16);

        # This is the way to do AES-256 using mcrypt PHP - its not AES-128 or anything other than that!
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $paddedCipherKey, $this->initializationVector);
        $encrypted = mcrypt_generic($td, $padded_plain_text);
        $encode = base64_encode($encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $encode;
    }

    public function decrypt($cipherText, $logger = null) {
        if (gettype($cipherText) != "string")
            return "DECRYPTION_ERROR";
        $decoded = base64_decode($cipherText);

        $shaCipherKey = hash("sha256", $this->cipherKey);
        $paddedCipherKey = substr($shaCipherKey, 0, 32);

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $paddedCipherKey, $this->initializationVector);

        $decrypted = mdecrypt_generic($td, $decoded); // TODO: handle non-encrypted unicode corner-case
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        $unPadded = $this->unPadPKCS7($decrypted, 16);

        return $this->tryToJsonDecode($unPadded);
    }
}
