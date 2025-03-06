<?php

/**
 * Oauth2KeyConfig is responsible for configuring, generating, and returning oauth2 keys that are used by the OpenEMR system.
 *
 * @package   openemr
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020-2025 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
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

    private CryptoGen $cryptoGen;

    private OAuth2KeyMissing $oauth2KeyMissing;

    public function __construct($siteDir = null)
    {
        if (empty($siteDir)) {
            // default to our global location
            $siteDir = $GLOBALS['OE_SITE_DIR'];
        }

        // Create a crypto object that will be used for encryption/decryption
        $this->cryptoGen = new CryptoGen();
        // verify and/or setup our key pairs.
        $this->privateKey = $siteDir . '/documents/certificates/oaprivate.key';
        $this->publicKey = $siteDir . '/documents/certificates/oapublic.key';

        // Create a Oauth2KeyMissing object that will track missing elements and used in below verifyKeys method and createOrRecreateKeys method
        $this->oauth2KeyMissing = new OAuth2KeyMissing();
        if ($this->verifyKeys() === false) {
            try {
                $this->createOrRecreateKeys();
            } catch (OAuth2KeyException $e) {
                // if unable to recreate keys, then force exit
                throw new OAuth2KeyException("Unable to create/recreate oauth2 keys" . $e->getMessage());
            }
        }
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
     * Configures and verifies the encryption key, passphrase, public key and private key for OpenEMR OAuth2.
     *  (note the existence of all these needed pieces have already been verified in the constructor, however
     *   to be safe will also check this here)
     *
     * @throws OAuth2KeyException
     */
    public function configKeyPairs(): void
    {
        //  collect the encryption key from database (confirm existence) and ensure it can by properly decrypted
        $eKey = sqlQueryNoLog("SELECT `name`, `value` FROM `keys` WHERE `name` = 'oauth2key'");
        if (!empty($eKey['name']) && ($eKey['name'] == 'oauth2key')) {
            $this->oaEncryptionKey = $this->cryptoGen->decryptStandard($eKey['value']);
            if (empty($this->oaEncryptionKey)) {
                // if decrypted key is empty, then critical error and must log and exit
                EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, "oauth2 ConfigKeyPairs: oauth2 encryption key was blank after it was decrypted");
                throw new OAuth2KeyException("oauth2 encryption key was blank after it was decrypted");
            }
        } else {
            // oauth2key is missing so must log and exit
            EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, "oauth2 ConfigKeyPairs: oauth2 encryption key is missing");
            throw new OAuth2KeyException("oauth2 encryption key is missing");
        }
        // collect the passphrase from database (confirm existence) and ensure it can by properly decrypted
        $pKey = sqlQueryNoLog("SELECT `name`, `value` FROM `keys` WHERE `name` = 'oauth2passphrase'");
        if (!empty($pKey['name']) && ($pKey['name'] == 'oauth2passphrase')) {
            $this->passphrase = $this->cryptoGen->decryptStandard($pKey['value']);
            if (empty($this->passphrase)) {
                // if decrypted pssphrase is empty, then critical error and must log and exit
                EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, "oauth2 ConfigKeyPairs: oauth2 passphrase was blank after it was decrypted");
                throw new OAuth2KeyException("oauth2 passphrase was blank after it was decrypted");
            }
        } else {
            // oauth2passphrase is missing so must log and exit
            EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, "oauth2 ConfigKeyPairs: oauth2 passphrase is missing");
            throw new OAuth2KeyException("oauth2 passphrase is missing");
        }
        // confirm existence of key pair
        if (!file_exists($this->privateKey) || !file_exists($this->publicKey)) {
            // key pair is missing so must log and exit
            EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, "oauth2 ConfigKeyPairs: oauth2 keypair is missing");
            throw new OAuth2KeyException("oauth2 keypair is missing");
        }
    }

    private function verifyKeys(): bool
    {
        $eKey = sqlQueryNoLog("SELECT `name`, `value` FROM `keys` WHERE `name` = 'oauth2key'");
        $pKey = sqlQueryNoLog("SELECT `name`, `value` FROM `keys` WHERE `name` = 'oauth2passphrase'");

        if (!$eKey || !$pKey || !file_exists($this->privateKey) || !file_exists($this->publicKey)) {
            // $this->oauth2KeyMissing will be used in the createOrRecreateKeys method to determine what is missing for logging purposes
            if (!$eKey) {
                $this->oauth2KeyMissing->setEncryptionKeyMissing();
            }
            if (!$pKey) {
                $this->oauth2KeyMissing->setPassphraseMissing();
            }
            if (!file_exists($this->privateKey)) {
                $this->oauth2KeyMissing->setPrivateKeyMissing();
            }
            if (!file_exists($this->publicKey)) {
                $this->oauth2KeyMissing->setPublicKeyMissing();
            }
            return false;
        }

        return true;
    }

    private function deleteKeys(): void
    {
        sqlStatementNoLog("DELETE FROM `keys` WHERE `name` = 'oauth2key'");
        sqlStatementNoLog("DELETE FROM `keys` WHERE `name` = 'oauth2passphrase'");

        if (file_exists($this->privateKey)) {
            unlink($this->privateKey);
        }
        if (file_exists($this->publicKey)) {
            unlink($this->publicKey);
        }
    }

    private function createOrRecreateKeys(): void
    {
        // Collect info from $this->oauth2KeyMissing to determine what is missing for logging purposes and then reset it
        $createNew = $this->oauth2KeyMissing->isMissingAll();
        if ($createNew) {
            $logLabel = "Attempt create the first oauth2 keys: ";
        } else {
            $logLabel = "Missing oauth2 keys (" . $this->oauth2KeyMissing->isMissingToString() . "), so attempt remove and recreate all the oauth2 keys: ";
        }
        $this->oauth2KeyMissing->reset();

        // Delete keys if they exist
        $this->deleteKeys();

        // Generate encryption key
        $this->oaEncryptionKey = RandomGenUtils::produceRandomBytes(32);
        if (empty($this->oaEncryptionKey)) {
            // if empty, then log and force exit
            EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, $logLabel . "random generator broken during oauth2 encryption key generation");
            throw new OAuth2KeyException("random generator broken during oauth2 encryption key generation");
        }
        $this->oaEncryptionKey = base64_encode($this->oaEncryptionKey);
        if (empty($this->oaEncryptionKey)) {
            // if empty, then log and force exit
            EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, $logLabel . "base64 encoding broken during oauth2 encryption key generation");
            throw new OAuth2KeyException("base64 encoding broken during oauth2 encryption key generation");
        }

        // Generate passphrase and public/private keys
        $this->passphrase = RandomGenUtils::produceRandomString(60, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
        if (empty($this->passphrase)) {
            // if empty, then log and force exit
            EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, $logLabel . "random generator broken during oauth2 key passphrase generation");
            throw new OAuth2KeyException("random generator broken during oauth2 key passphrase generation");
        }
        $keysConfig = [
            "default_md" => "sha256",
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "private_key_bits" => 2048,
            "encrypt_key" => true,
            "encrypt_key_cipher" => OPENSSL_CIPHER_AES_256_CBC
        ];
        $msg_error = $msgEnv = getenv('OPENSSL_CONF') . "\n";
        $keys = openssl_pkey_new($keysConfig);
        if (!$keys) {
            while ($msg = openssl_error_string()) {
                $msg_error .= $msg . "\n";
            }
            error_log($msg_error);
            // if unable to create keys, then log and force exit
            EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, $logLabel . "key generation broken OPEN_SSL: $msgEnv" . $msg_error);
            throw new OAuth2KeyException("key generation broken OPEN_SSL: $msgEnv" . $msg_error);
        }
        $privkey = '';
        openssl_pkey_export($keys, $privkey, $this->passphrase, $keysConfig);
        $pubkey = openssl_pkey_get_details($keys);
        $pubkey = $pubkey["key"];
        if (empty($privkey) || empty($pubkey)) {
            // if unable to construct keys, then log and force exit
            EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 0, $logLabel . "key construction broken during oauth2");
            throw new OAuth2KeyException("key construction broken during oauth2");
        }

        // Successfully created encryption/public/private keys and passphrase, so store them and log success
        file_put_contents($this->privateKey, $privkey);
        chmod($this->privateKey, 0640);
        file_put_contents($this->publicKey, $pubkey);
        chmod($this->publicKey, 0660);
        sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES ('oauth2key', ?)", [$this->cryptoGen->encryptStandard($this->oaEncryptionKey)]);
        sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES ('oauth2passphrase', ?)", [$this->cryptoGen->encryptStandard($this->passphrase)]);
        EventAuditLogger::instance()->newEvent("oauth2", ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 1, $logLabel . "Successful");
    }
}
