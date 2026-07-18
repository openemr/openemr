<?php

/**
 * Voice Module Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\RCVoice;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use Throwable;

trait VoiceFunctionsTrait
{
    public function install(): string
    {
        // Call-event tracking is a RingCentral-only, strictly opt-in feature.
        // Touch no RC API unless BOTH are true:
        //   1. voice (RingCentral) is enabled  -> oe_enable_voice global
        //   2. the admin turned on event tracking in Voice setup -> enable_events
        // This keeps webhook subscription registration an explicit admin action
        // instead of a per-page side effect, and enforces "no RC voice => no
        // events enable".
        if (empty(OEGlobalsBag::getInstance()->get('oe_enable_voice'))) {
            return (string) json_encode([
                'status' => 'DISABLED',
                'msg' => xlt('Voice (RingCentral) is not enabled, so call event tracking cannot be registered.'),
            ]);
        }
        if (empty($this->credentials['enable_events'] ?? null)) {
            return (string) json_encode([
                'status' => 'DISABLED',
                'msg' => xlt('Call event tracking is off. Enable it in Voice setup and save before registering.'),
            ]);
        }

        $response = null;
        try {
            // Persist (encrypted at rest) the server-readable webhook secret so
            // it is ready when the RingCentral event subscription lands. The
            // subscription registration itself is deferred to a follow-up, so no
            // RC API call is made here yet.
            $this->getOrCreateWebhookSecret();
            $response = [
                'status' => 'SUCCESS',
                'msg' => xlt('Voice webhook secret provisioned. Event subscription registration is not yet available.'),
            ];
        } catch (Throwable $e) {
            ServiceContainer::getLogger()->error('Voice webhook secret provisioning failed', ['exception' => $e]);
            $response = ['status' => 'ERROR', 'msg' => xlt('Webhook secret provisioning failed. See server log.')];
        }
        return (string) json_encode($response);
    }

    /**
     * Fetch the persisted RingCentral webhook secret, creating and storing one
     * (encrypted at rest) on first use.
     *
     * Stored under a fixed (auth_user=0, vendor='_voice_webhook') row in
     * module_faxsms_credentials so the session-less voice_webhook.php can read
     * it back to authenticate deliveries. One secret per OpenEMR database
     * (i.e. per site for the standard one-DB-per-site layout).
     */
    private function getOrCreateWebhookSecret(): string
    {
        $existing = $this->loadWebhookSecret();
        if ($existing !== '') {
            return $existing;
        }

        $secret = bin2hex(random_bytes(32));
        $content = $this->crypto->encryptStandard((string)json_encode(['secret' => $secret]));
        // Insert without overwriting a row a concurrent install() may have just
        // written; the no-op UPDATE keeps the existing (already-registered) secret.
        sqlQuery(
            "INSERT INTO `module_faxsms_credentials` (`auth_user`, `vendor`, `credentials`)
             VALUES (0, ?, ?)
             ON DUPLICATE KEY UPDATE `updated` = `updated`",
            ['_voice_webhook', $content]
        );

        // Read back the persisted secret (ours, or the winner of a race).
        $stored = $this->loadWebhookSecret();

        return $stored !== '' ? $stored : $secret;
    }

    /**
     * Read and decrypt the persisted webhook secret, or '' when absent/undecryptable.
     */
    private function loadWebhookSecret(): string
    {
        $row = sqlQuery(
            "SELECT `credentials` FROM `module_faxsms_credentials` WHERE `auth_user` = 0 AND `vendor` = ?",
            ['_voice_webhook']
        );
        if (!empty($row['credentials'])) {
            $plain = $this->crypto->decryptStandard((string)$row['credentials']);
            $data = json_decode((string)$plain, true);
            if (is_array($data) && !empty($data['secret']) && is_string($data['secret'])) {
                return $data['secret'];
            }
        }

        return '';
    }
}
