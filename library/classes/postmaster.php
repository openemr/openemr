<?php

/**
 * MyMailer class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Open Support LLC
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Crypto\CryptoGen;
use PHPMailer\PHPMailer\PHPMailer;

class MyMailer extends PHPMailer
{
    var $Mailer;
    var $SMTPAuth;
    var $Host;
    var $Username;
    var $Password;
    var $Port;
    var $CharSet;

    function __construct($throwExceptions = false)
    {
        // make sure we initiate our constructor here...
        parent::__construct($throwExceptions);

        $this->emailMethod();
    }

    /**
     * Checks if the MyMailer service is configured for mail with all of the host parameters defined
     * @return bool
     */
    public static function isConfigured()
    {
        switch ($GLOBALS['EMAIL_METHOD']) {
            case "SMTP":
                $requiredKeys = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_SECURE'];
                if ($GLOBALS['SMTP_Auth']) {
                    $requiredKeys[] = 'SMTP_USER';
                    $requiredKeys[] = 'SMTP_PASS';
                }
                break;
            default:
                $requiredKeys = [];
                break;
        }

        foreach ($requiredKeys as $key) {
            if (empty($GLOBALS[$key])) {
                return false;
            }
        }
        return true;
    }

    function emailMethod()
    {
        global $HTML_CHARSET;
        $this->CharSet = $HTML_CHARSET;
        switch ($GLOBALS['EMAIL_METHOD']) {
            case "PHPMAIL":
                $this->Mailer = "mail";
                break;
            case "SMTP":
                global $SMTP_Auth;
                $this->Mailer = "smtp";
                $this->SMTPAuth = $SMTP_Auth;
                $this->Host = $GLOBALS['SMTP_HOST'];
                $this->Username = $GLOBALS['SMTP_USER'];
                $cryptoGen = new CryptoGen();
                $this->Password = $cryptoGen->decryptStandard($GLOBALS['SMTP_PASS']);
                $this->Port = $GLOBALS['SMTP_PORT'];
                $this->SMTPSecure = $GLOBALS['SMTP_SECURE'];
                break;
            case "SENDMAIL":
                $this->Mailer = "sendmail";
                break;
        }
    }
}
