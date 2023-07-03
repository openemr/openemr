<?php

/**
 * Oauth2KeyConfig is responsible for configuring, generating, and returning oauth2 keys that are used by the OpenEMR system.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Utils\RandomGenUtils;

class OAuth2KeyConfig
{
    /**
     * @var string encryption key stored in the database
     */
    private $oaEncryptionKey;

    /**
     * @var string OAUTH2 passphrase used for the private key
     */
    private $passphrase;

    /**
     * @var string File location of the oauth2 private key
     */
    private $privateKey;

    /**
     * @var string File location of the oauth2 public key
     */
    private $publicKey;

    public function __construct($siteDir = null)
    {
        if (empty($siteDir)) {
            // default to our global location
            $siteDir = $GLOBALS['OE_SITE_DIR'];
        }

        // Create a crypto object that will be used for for encryption/decryption
        $this->cryptoGen = new CryptoGen();
        // verify and/or setup our key pairs.
        $this->privateKey = $siteDir . '/documents/certificates/oaprivate.key';
        $this->publicKey = $siteDir . '/documents/certificates/oapublic.key';
    }

    public function getPassPhrase()
    {
        return $this->passphrase;
    }

    public function getEncryptionKey()
    {
        return $this->oaEncryptionKey;
    }
    public function getPublicKeyLocation()
    {
        return $this->publicKey;
    }

    public function getPrivateKeyLocation()
    {
        return $this->privateKey;
    }

    /**
     * Configures the public and private keys for OpenEMR OAuth2.  If they do not exist it generates them.
     * @throws OAuth2KeyException
     */
    public function configKeyPairs(): void
    {
        // encryption key
        $eKey = sqlQueryNoLog("SELECT `name`, `value` FROM `keys` WHERE `name` = 'oauth2key'");
        if (!empty($eKey['name']) && ($eKey['name'] === 'oauth2key')) {
            // collect the encryption key from database
            $this->oaEncryptionKey = $this->cryptoGen->decryptStandard($eKey['value']);
            if (empty($this->oaEncryptionKey)) {
                if (($_ENV['OPENEMR__ENVIRONMENT'] ?? '') === 'dev') {
                    // delete corrupted key if doesn't exist to regenerate on next attempt.
                    sqlStatementNoLog("DELETE FROM `keys` WHERE `name` = 'oauth2key'");
                }
                // if decrypted key is empty, then critical error and must exit
                throw new OAuth2KeyException("oauth2 key problem after decrypted. Key is invalid, Try to restore the file key in sites from a backup.");
            }
        } else {
            // create a encryption key and store it in database
            $this->oaEncryptionKey = RandomGenUtils::produceRandomBytes(32);
            if (empty($this->oaEncryptionKey)) {
                // if empty, then force exit
                throw new OAuth2KeyException("random generator broken during oauth2 encryption key generation");
            }
            $this->oaEncryptionKey = base64_encode($this->oaEncryptionKey);
            if (empty($this->oaEncryptionKey)) {
                // if empty, then force exit
                throw new OAuth2KeyException("base64 encoding broken during oauth2 encryption key generation");
            }
            sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES ('oauth2key', ?)", [$this->cryptoGen->encryptStandard($this->oaEncryptionKey)]);
        }
        // private key
        if (!file_exists($this->privateKey)) {
            // create the private/public key pair (store in filesystem) with a random passphrase (store in database)
            // first, create the passphrase (removing any prior passphrases)
            sqlStatementNoLog("DELETE FROM `keys` WHERE `name` = 'oauth2passphrase'");
            $this->passphrase = RandomGenUtils::produceRandomString(60, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
            if (empty($this->passphrase)) {
                // if empty, then force exit
                throw new OAuth2KeyException("random generator broken during oauth2 key passphrase generation");
            }
            // second, create and store the private/public key pair
            $keysConfig = [
                "default_md" => "sha256",
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
                "private_key_bits" => 2048,
                "encrypt_key" => true,
                "encrypt_key_cipher" => OPENSSL_CIPHER_AES_256_CBC
            ];
            $keys = \openssl_pkey_new($keysConfig);
            if ($keys === false) {
                // if unable to create keys, then force exit
                throw new OAuth2KeyException("key generation broken during oauth2");
            }
            $privkey = '';
            openssl_pkey_export($keys, $privkey, $this->passphrase, $keysConfig);
            $pubkey = openssl_pkey_get_details($keys);
            $pubkey = $pubkey["key"];
            if (empty($privkey) || empty($pubkey)) {
                // if unable to construct keys, then force exit
                throw new OAuth2KeyException("key construction broken during oauth2");
            }
            // third, store the keys on drive and store the passphrase in the database
            file_put_contents($this->privateKey, $privkey);
            chmod($this->privateKey, 0640);
            file_put_contents($this->publicKey, $pubkey);
            chmod($this->publicKey, 0660);
            sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES ('oauth2passphrase', ?)", [$this->cryptoGen->encryptStandard($this->passphrase)]);
        }
        // confirm existence of passphrase
        $pkey = sqlQueryNoLog("SELECT `name`, `value` FROM `keys` WHERE `name` = 'oauth2passphrase'");
        if (!empty($pkey['name']) && ($pkey['name'] == 'oauth2passphrase')) {
            $this->passphrase = $this->cryptoGen->decryptStandard($pkey['value']);
            if (empty($this->passphrase)) {
                // if decrypted pssphrase is empty, then critical error and must exit
                throw new OAuth2KeyException("oauth2 passphrase was blank after it was decrypted");
            }
        } else {
            // oauth2passphrase is missing so must exit
            throw new OAuth2KeyException("oauth2 passphrase is missing");
        }
        // confirm existence of key pair
        if (!file_exists($this->privateKey) || !file_exists($this->publicKey)) {
            // key pair is missing so must exit
            throw new OAuth2KeyException("oauth2 keypair is missing");
        }
    }
}
