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
    private $appKey;
    private $phone;

    public function __construct()
    {
        if (empty($GLOBALS['oefax_enable_sms'] ?? null)) {
            throw new \RuntimeException(xlt("Access denied! Module not enabled"));
        }
        parent::__construct();
    }

    /**
     * @return array|mixed
     */
    public function getCredentials(): mixed
    {
        $credentials = appDispatch::getSetup();

        $this->appKey = $credentials['appKey'];
        $this->phone = $credentials['phone'];

        return $credentials;
    }

    /**
     * @return string
     */
    public function sendSMS(string $toPhone = '', string $subject = '', string $message = '', string $from = ''): string
    {
        // $subject and $from do not have valid/meaningful values passed in

        /* Reformat $toPhone number */
        $cleanup_chr = array ("+", " ", "(", ")", "\r", "\n", "\r\n");
        $toPhone = str_replace($cleanup_chr, "", $toPhone);
        if (!str_starts_with($toPhone, "1")) {
            $toPhone = "1" . $toPhone;
        }

        $url = sprintf(
            "https://platform.clickatell.com/messages/http/send?apiKey=%s&to=%s&from=%s&content=%s",
            $this->appKey,
            $toPhone,
            $this->phone,
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
                $response = $json['messages'][0]['apiMessageId'];
            }
        }

        return text($response);
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
}
