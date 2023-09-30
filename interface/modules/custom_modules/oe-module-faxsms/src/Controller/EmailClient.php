<?php

/**
 * Email Controller
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use MyMailer;
use OpenEMR\Common\Crypto\CryptoGen;
use Symfony\Component\HttpClient\HttpClient;

class EmailClient extends AppDispatch
{
    public static $timeZone;
    public $baseDir;
    public $uriDir;
    public $serverUrl;
    public $credentials;
    public string $portalUrl;
    protected $crypto;
    private EmailClient $client;

    public function __construct()
    {
        if (empty($GLOBALS['oe_enable_email'] ?? null)) {
            throw new \RuntimeException(xlt("Access denied! Module not enabled"));
        }
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['temporary_files_dir'];
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'];
        parent::__construct();
    }

    /**
     * @return array|mixed
     */
    public function getCredentials(): mixed
    {
        $credentials = appDispatch::getSetup();

        $this->sid = $credentials['username'];
        $this->appKey = $credentials['appKey'];
        $this->appSecret = $credentials['appSecret'];
        $this->serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
                "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $this->uriDir = $this->serverUrl . $this->uriDir;

        return $credentials;
    }

    /**
     * @return bool|string
     */

    /**
     * @return string
     */
    public function sendSMS(): string
    {
        // dummy function
        return text("Not implemented");
    }

    /**
     * @return mixed|string
     */
    public function sendFax(): string|bool
    {
        // dummy function
        return text("Not implemented");
    }

    /**
     * @param $acl
     * @return int
     */
    public function authenticate($acl = ['admin', 'doc']): int
    {
        list($s, $v) = $acl;
        return $this->verifyAcl($s, $v);
    }

    /**
     * @return string
     */
    public function sendEmail(): string
    {
        $statusMsg = xlt("Email Requests") . "<br />";
        $body = $this->getRequest('comments');
        $email = $this->getRequest('email');
        $hasEmail = $this->validEmail($email);
        $subject = $this->getRequest('subject', xl("Private confidential message"));
        $user = $this::getLoggedInUser();
        $htmlContent = $this->getRequest('html_content');
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        if (!$hasEmail) {
            return js_escape(xlt("Error: Missing email address. Try again."));
        }
        $statusMsg .= $this->mailEmail($email, $from_name, $body, $subject, $htmlContent);
        return js_escape($statusMsg);
    }

    /**
     * @return false|string
     */
    public function getUser(): bool|string
    {
        $id = $this->getRequest('uid');
        $query = "SELECT * FROM users WHERE id = ?";
        $result = sqlStatement($query, array($id));
        $u = array();
        foreach ($result as $row) {
            $u[] = $row;
        }
        $u = $u[0];
        $r = array($u['fname'], $u['lname'], $u['fax'], $u['facility'], $u['email']);

        return json_encode($r);
    }

    /**
     * @return null
     */
    protected function index()
    {
        if (!$this->getSession('pid', '')) {
            $pid = $this->getRequest('patient_id');
            $this->setSession('pid', $pid);
        } else {
            $pid = $this->getSession('pid', '');
        }

        return null;
    }

    /**
     * @return string|bool
     */
    function fetchReminderCount(): string|bool
    {
        // TODO: Implement fetchReminderCount() method.
    }
}
