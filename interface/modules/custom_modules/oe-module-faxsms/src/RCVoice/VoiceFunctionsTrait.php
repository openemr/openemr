<?php

/**
 * Voice Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\RCVoice;

use Exception;
use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\Platform\Platform;

trait VoiceFunctionsTrait
{
    /** @var Platform */
    protected $platform;
    protected string $webhookUrl;
    protected $token;

    /**
     * GET /interface/modules/custom_modules/oe-module-faxsms/index.php?module=FaxSMS&controller=RCFaxClient&action=getSipProvision
     * Proxies the RingCentral sip‑provision?sipInfo=true call.
     */
    public function getSipProvision(): string
    {
        // Ensure we’re authenticated
        $auth = $this->authenticate();
        if ($auth !== 1) {
            http_response_code(401);
            header('Content-Type: application/json');
            return json_encode(['error' => 'Authentication failed']);
        }

        try {
            // Call the client‑info provisioning endpoint
            $resp = $this->platform->post(
                '/client-info/sip-provision',
                ['sipInfo' => [['transport' => 'WSS']]]
            );

            // Get raw JSON and decode as associative array
            $body = $resp->text();
            $data = json_decode((string) $body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON from SIP provision');
            }

            // Pull out the first sipInfo entry
            if (empty($data['sipInfo'])) {
                throw new \RuntimeException('No sipInfo returned');
            }
            $sipInfo = is_array($data['sipInfo'])
                ? $data['sipInfo'][0]
                : $data['sipInfo'];

            header('Content-Type: application/json');
            return json_encode($sipInfo);
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            return json_encode(['error' => $e->getMessage()]);
        }
    }


    /**
     * Initialize the Platform instance (call from your constructor).
     */
    protected function initVoice($platform): void
    {
        $this->platform = $platform;
        $this->install();
    }

    /**
     * Make an outbound call (RingOut).
     */
    public function makeRingOutCall($toNumber = null, $fromNumber = null, $callerId = null)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $toNumber = $toNumber ?: $this->getRequest('toPhone');
        $fromNumber = $fromNumber ?: $this->getRequest('fromPhone');
        $userNumber = $this->credentials['smsNumber'] ?? $this->credentials['phone'] ?? '';
        $defaultNumber = $this->formatPhone($userNumber);
        $fromNumber = $this->formatPhone($fromNumber ?: $defaultNumber);
        $toNumber = $this->formatPhone($toNumber);

        try {
            $body = [
                'from' => ['phoneNumber' => $fromNumber],
                'to' => ['phoneNumber' => $toNumber],
                'playPrompt' => false,
            ];

            if ($callerId) {
                $body['callerId'] = ['phoneNumber' => $fromNumber];
            }

            $response = $this->platform->post('/restapi/v1.0/account/~/extension/~/ring-out', $body);
            $result = ['msg' => 'RingOut call status: ' . $response->json()->status->callStatus];
            return json_encode($result);
        } catch (Exception $e) {
            $result = ['error' => 'RingOut Error: ' . $e->getMessage()];
            return json_encode($result);
        }
    }

    /**
     * Get voicemail message content.
     */
    public function getVoicemailAttachment(string $messageId): string|false
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }
        if (empty($messageId)) {
            return false; // Invalid message ID
        }

        try {
            $endpoint = "/restapi/v1.0/account/~/extension/~/message-store/{$messageId}/content";
            $response = $this->platform->get($endpoint);
            return $response->raw(); // binary content for WAV/MP3
        } catch (Exception) {
            return false;
        }
    }

    public function install()
    {
        try {
            $token = $_SESSION['ringcentral_voice_token'] ?? 'changeme';
            if ($token == 'changeme') {
                // Generate secure token
                $token = bin2hex(random_bytes(16));
                $_SESSION['ringcentral_voice_token'] = $token;
            }
            // Webhook endpoint
            $this->webhookUrl = $this->getWebhookUrl($token);
            $this->token = $token;
            // Create webhook
            $response = $this->createSubscription();
        } catch (\Exception $e) {
            error_log("Installation failed: " . $e->getMessage());
        }
        return  json_encode($response);
    }

    protected function getWebhookUrl(string $token): string
    {
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

        return $protocol . $serverName . "/interface/modules/custom_modules/oe-module-faxsms/library//phone-services/voice_webhook.php";
    }

    public function createSubscription(): array|string
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            $error['status'] = 'ERROR';
            $error['msg'] = "Authentication failed: " . $authErrorMsg;
            return $error;
        }

        $result = [];
        $result['status'] = 'SUCCESS';
        $result['id'] = '';
        $result['msg'] = '';

        try {
            $response = $this->platform->get('/restapi/v1.0/subscription');
            $subscriptions = $response->json()->records;

            $expectedWebhookUrl = $this->webhookUrl . '?token=' . urlencode($this->token);
            $expectedEventFilter = '/restapi/v1.0/account/~/extension/~/telephony/sessions';
            $existingSubscription = null;
            $subscriptionsToDelete = [];
            // Check existing subscriptions
            foreach ($subscriptions as $subscription) {
                // Check if any event filter matches the telephony sessions pattern
                $isCorrectFilter = false;
                foreach ($subscription->eventFilters ?? [] as $filter) {
                    if (preg_match('#^/restapi/v1\.0/account/\d+/extension/\d+/telephony/sessions$#', (string) $filter)) {
                        $isCorrectFilter = true;
                        break;
                    }
                }
                $isCorrectWebhook = ($subscription->deliveryMode->transportType ?? '') === 'WebHook'
                    && ($subscription->deliveryMode->address ?? '') === $expectedWebhookUrl;
                $isActive = ($subscription->status ?? '') === 'Active';

                if ($isCorrectFilter && $isCorrectWebhook && $isActive) {
                    // Found matching active subscription
                    $existingSubscription = $subscription;
                } else {
                    // Mark for deletion if it's our webhook but with wrong config, or inactive
                    if (
                        isset($subscription->deliveryMode->address) &&
                        str_contains($subscription->deliveryMode->address, $this->webhookUrl)
                    ) {
                        $subscriptionsToDelete[] = $subscription->id;
                    }
                }
            }

            // Delete outdated/incorrect subscriptions
            foreach ($subscriptionsToDelete as $subscriptionId) {
                $this->platform->delete("/restapi/v1.0/subscription/{$subscriptionId}");
            }

            if ($existingSubscription) {
                // Use existing subscription
                $result['msg'] = "Using existing webhook subscription: " . $existingSubscription->id;
            } else {
                // Create new subscription
                $response = $this->platform->post('/subscription', [
                    'eventFilters' => [$expectedEventFilter],
                    'deliveryMode' => [
                        'transportType' => 'WebHook',
                        'address' => $expectedWebhookUrl
                    ]
                ]);

                $subscription = $response->json();
                $subscriptionId = $subscription->id;
                $result['id'] = $subscriptionId;
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "ERROR: " . $e->getMessage() . PHP_EOL;
        }

        return $result;
    }

    /**
     * Answer an incoming call
     */
    public function answerCall($telephonySessionId, $partyId)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $result = [];
        $result['status'] = 'SUCCESS';

        try {
            $response = $this->platform->post(
                "/restapi/v1.0/account/~/telephony/sessions/{$telephonySessionId}/parties/{$partyId}",
                [
                    'action' => 'answer'
                ]
            );

            if ($response->response()->getStatusCode() === 200) {
                $result['msg'] = "Successfully answered call - Session: {$telephonySessionId}, Party: {$partyId}";
            } else {
                $result['status'] = 'ERROR';
                $result['msg'] = "Failed to answer call - HTTP Status: " . $response->response()->getStatusCode();
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "Error answering call: " . $e->getMessage();
        }

        return json_encode($result);
    }

    /**
     * Reject/decline an incoming call
     */
    public function rejectCall($telephonySessionId, $partyId)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $result = [];
        $result['status'] = 'SUCCESS';

        try {
            $response = $this->platform->post(
                "/restapi/v1.0/account/~/telephony/sessions/{$telephonySessionId}/parties/{$partyId}",
                [
                    'action' => 'reject'
                ]
            );

            if ($response->response()->getStatusCode() === 200) {
                $result['msg'] = "Successfully rejected call - Session: {$telephonySessionId}, Party: {$partyId}";
            } else {
                $result['status'] = 'ERROR';
                $result['msg'] = "Failed to reject call - HTTP Status: " . $response->response()->getStatusCode();
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "Error rejecting call: " . $e->getMessage();
        }

        return json_encode($result);
    }

    /**
     * Put a call on hold
     */
    public function holdCall($telephonySessionId, $partyId)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $result = [];
        $result['status'] = 'SUCCESS';

        try {
            $response = $this->platform->post(
                "/restapi/v1.0/account/~/telephony/sessions/{$telephonySessionId}/parties/{$partyId}",
                [
                    'action' => 'hold'
                ]
            );

            if ($response->response()->getStatusCode() === 200) {
                $result['msg'] = "Successfully put call on hold - Session: {$telephonySessionId}, Party: {$partyId}";
            } else {
                $result['status'] = 'ERROR';
                $result['msg'] = "Failed to hold call - HTTP Status: " . $response->response()->getStatusCode();
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "Error holding call: " . $e->getMessage();
        }

        return json_encode($result);
    }

    /**
     * Take a call off hold (unhold)
     */
    public function unholdCall($telephonySessionId, $partyId)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $result = [];
        $result['status'] = 'SUCCESS';

        try {
            $response = $this->platform->post(
                "/restapi/v1.0/account/~/telephony/sessions/{$telephonySessionId}/parties/{$partyId}",
                [
                    'action' => 'unhold'
                ]
            );

            if ($response->response()->getStatusCode() === 200) {
                $result['msg'] = "Successfully took call off hold - Session: {$telephonySessionId}, Party: {$partyId}";
            } else {
                $result['status'] = 'ERROR';
                $result['msg'] = "Failed to unhold call - HTTP Status: " . $response->response()->getStatusCode();
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "Error unholding call: " . $e->getMessage();
        }

        return json_encode($result);
    }

    /**
     * Hangup/disconnect a call
     */
    public function hangupCall($telephonySessionId, $partyId)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $result = [];
        $result['status'] = 'SUCCESS';

        try {
            $response = $this->platform->delete(
                "/restapi/v1.0/account/~/telephony/sessions/{$telephonySessionId}/parties/{$partyId}"
            );

            if ($response->response()->getStatusCode() === 204) {
                $result['msg'] = "Successfully hung up call - Session: {$telephonySessionId}, Party: {$partyId}";
            } else {
                $result['status'] = 'ERROR';
                $result['msg'] = "Failed to hangup call - HTTP Status: " . $response->response()->getStatusCode();
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "Error hanging up call: " . $e->getMessage();
        }

        return json_encode($result);
    }

    /**
     * Transfer a call to another extension or phone number
     */
    public function transferCall($telephonySessionId, $partyId, $transferTarget)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $result = [];
        $result['status'] = 'SUCCESS';

        try {
            $response = $this->platform->post(
                "/restapi/v1.0/account/~/telephony/sessions/{$telephonySessionId}/parties/{$partyId}",
                [
                    'action' => 'transfer',
                    'target' => [
                        'phoneNumber' => $transferTarget
                    ]
                ]
            );

            if ($response->response()->getStatusCode() === 200) {
                $result['msg'] = "Successfully transferred call to {$transferTarget} - Session: {$telephonySessionId}, Party: {$partyId}";
            } else {
                $result['status'] = 'ERROR';
                $result['msg'] = "Failed to transfer call - HTTP Status: " . $response->response()->getStatusCode();
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "Error transferring call: " . $e->getMessage();
        }

        return json_encode($result);
    }

    /**
     * Start recording a call
     */
    public function startRecording($telephonySessionId)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $result = [];
        $result['status'] = 'SUCCESS';

        try {
            $response = $this->platform->post(
                "/restapi/v1.0/account/~/telephony/sessions/{$telephonySessionId}/recording",
                []
            );

            if ($response->response()->getStatusCode() === 200) {
                $recordingData = $response->json();
                $result['msg'] = "Successfully started recording - Session: {$telephonySessionId}, Recording ID: " . $recordingData->id;
                $result['recording_id'] = $recordingData->id;
            } else {
                $result['status'] = 'ERROR';
                $result['msg'] = "Failed to start recording - HTTP Status: " . $response->response()->getStatusCode();
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "Error starting recording: " . $e->getMessage();
        }

        return json_encode($result);
    }

    /**
     * Stop recording a call
     */
    public function stopRecording($telephonySessionId, $recordingId)
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
        }

        $result = [];
        $result['status'] = 'SUCCESS';

        try {
            $response = $this->platform->patch(
                "/restapi/v1.0/account/~/telephony/sessions/{$telephonySessionId}/recording/{$recordingId}",
                [
                    'active' => false
                ]
            );

            if ($response->response()->getStatusCode() === 200) {
                $result['msg'] = "Successfully stopped recording - Session: {$telephonySessionId}, Recording ID: {$recordingId}";
            } else {
                $result['status'] = 'ERROR';
                $result['msg'] = "Failed to stop recording - HTTP Status: " . $response->response()->getStatusCode();
            }
        } catch (ApiException $e) {
            $result['status'] = 'ERROR';
            $result['msg'] = "Error stopping recording: " . $e->getMessage();
        }

        return json_encode($result);
    }
}
