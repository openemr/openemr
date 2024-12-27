<?php

/**
 * Clickatell SMS Controller
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

class ClickatellSMSClient extends AppDispatch
{
    public function __construct()
    {
        if (empty($GLOBALS['oefax_enable_sms'] ?? null)) {
            throw new \RuntimeException(xlt("Access denied! Module not enabled"));
        }
        parent::__construct();
    }

    /**
     * @return string
     */
    public function sendSMS($toPhone = '', string $subject = '', string $message = '', string $from = ''): string
    {
        // If this is made as an API call we need to check authorization.
        $authErrorMsg = $this->authenticate(); // currently default is only admin can send SMS. check with author!
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        // If this is invoked from the UI via AppDispatch::dispatchAction(), the
        // values won't be parameters, but instead will come from the request.
        $toPhone = $toPhone ?: $this->getRequest('phone');
        $message = $message ?: $this->getRequest('comments');

        /* Reformat $toPhone number */
        $cleanup_chr = array ("+", " ", "(", ")", "\r", "\n", "\r\n");
        $toPhone = str_replace($cleanup_chr, "", $toPhone);
        if (!str_starts_with($toPhone, "1")) {
            $toPhone = "1" . $toPhone;
        }

        $url = sprintf(
            "https://platform.clickatell.com/messages/http/send?apiKey=%s&to=%s&from=%s&content=%s",
            $this->credentials['appKey'],
            $toPhone,
            $this->credentials['phone'],
            rawurlencode($message)
        );
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
            ],
        ]);
        $response = file_get_contents($url, false, $context);

        $json = json_decode($response, true);
        if ($json['responseCode'] < 400) {
            if ($json['messages'][0]['accepted']) {
                return xlt('Message Sent');
            }
        }

        return text('Error: ' . $response);
    }

    /**
     * @return mixed|string
     */
    public function sendFax(): string|bool
    {
        return text("Not supported");
    }

    /**
     * @return string
     */
    public function sendEmail(): string
    {
        return text("Not supported");
    }

    /**
     * @return string|bool
     */
    function fetchReminderCount(): string|bool
    {
        return 0;
    }

    /**
     * @param $uiDateRangeFlag
     * @return false|string|null
     */
    public function fetchSMSList($uiDateRangeFlag = true): false|string|null
    {
        return "[]"; // Caller expects JSON result, not HTML;
    }

    /**
     * @return string
     */
    public function getCallLogs()
    {
        return xlt('Not Supported');
    }

    /**
     * @param $acl
     * @return int
     */
    function authenticate($acl = ['patients', 'appt']): int
    {
        list($s, $v) = $acl;
        return $this->verifyAcl($s, $v);
    }
}
