<?php

/**
 * AuthGlobal class.
 *
 *   Support for authentication of encrypted hash in a global setting
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Crypto\CryptoGen;

class AuthGlobal
{
    /**
     * @var string
     */
    private $globalSetting;

    public function __construct($globalSetting)
    {
        $this->globalSetting = $globalSetting;
    }

    public function globalVerify(string $pass): bool
    {
        if (empty($pass) || empty($this->globalSetting) || empty($GLOBALS[$this->globalSetting])) {
            return false;
        }

        // collect and decrypt the global hash
        $cryptoGen = new CryptoGen();
        $globalHash = $cryptoGen->decryptStandard($GLOBALS[$this->globalSetting]);

        if (empty($globalHash)) {
            return false;
        }

        // authenticate
        if ((!AuthHash::hashValid($globalHash)) || (!AuthHash::passwordVerify($pass, $globalHash))) {
            return false;
        }

        // success, now rehash if needed
        $authHash = new AuthHash();
        if ($authHash->passwordNeedsRehash($globalHash)) {
            $newHash = $authHash->passwordHash($pass);
            $newHash = $cryptoGen->encryptStandard($newHash);
            sqlStatement("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = ?", [$newHash, $this->globalSetting]);
        }

        return true;
    }
}
