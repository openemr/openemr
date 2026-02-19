<?php

/**
 * Claim file upload service for ClaimRev.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Billing\BillingProcessor\X12RemoteTracker;
use OpenEMR\Services\BaseService;

class ClaimUpload extends BaseService
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_PARAMETER_ERROR = 'parameter-error';
    public const STATUS_CLAIM_FILE_ERROR = 'claim-file-error';
    public const STATUS_LOGIN_ERROR = 'login-error';
    public const STATUS_CHDIR_ERROR = 'chdir-error';
    public const STATUS_IN_PROGRESS = 'in-progress';
    public const STATUS_UPLOAD_ERRROR = 'upload-error';
    public const STATUS_SUCCESS = 'success';

    public const TABLE_NAME = 'x12_remote_tracker';


    protected static $x12_partner_field_keys = [
        'x12_sftp_host' => 'X12 SFTP Host',
        'x12_sftp_port' => 'X12 SFTP Port',
        'x12_sftp_login' => 'X12 SFTP Login',
        'x12_sftp_pass' => 'X12 SFTP Password',
        'x12_sftp_remote_dir' => 'X12 SFTP Remote Dir',
        'x12_sftp_local_dir' => 'X12 SFTP Local Dir',
    ];

    public const SELECT = "SELECT R.id, R.x12_filename, R.status, R.messages, R.claims, R.created_at, R.updated_at,
        P.name, P.id AS x12_partner_id, P.x12_sftp_host, P.x12_sftp_port, P.x12_sftp_login, P.x12_sftp_pass,
        P.x12_sftp_remote_dir, P.x12_sftp_local_dir FROM x12_remote_tracker R";

    protected $validationMessages = [];

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public static function sendWaitingFiles(): void
    {
        $remoteTracker = new X12RemoteTracker();
        $x12_remotes = $remoteTracker->fetchByStatus(self::STATUS_WAITING);

        try {
            $api = ClaimRevApi::makeFromGlobals();
        } catch (ClaimRevAuthenticationException) {
            // Mark all waiting files as login error
            foreach ($x12_remotes as $x12_remote) {
                $x12_remote['status'] = self::STATUS_LOGIN_ERROR;
                $x12_remote['messages'] = 'Invalid Username or Password.';
                $remoteTracker->update($x12_remote);
            }
            return;
        }

        foreach ($x12_remotes as $x12_remote) {
            /** @var string */
            $x12_remoteFilename = $x12_remote['x12_filename'];
            // Make sure local claim file exists and we have permission to read it
            /** @var string */
            $localDir = $x12_remote['x12_sftp_local_dir'];
            $claim_file = $localDir . $x12_remoteFilename;
            if (!file_exists($claim_file)) {
                $claim_file = $GLOBALS['OE_SITE_DIR'] . '/documents/edi/' . $x12_remoteFilename;
            }

            $claim_file_contents = file_get_contents($claim_file);
            if ($claim_file_contents === false) {
                $x12_remote['status'] = self::STATUS_CLAIM_FILE_ERROR;
                $x12_remote['messages'] = "Could not open local claim file: `$claim_file`";
                $remoteTracker->update($x12_remote);
                continue;
            }

            // Change status from waiting to in-progress
            $x12_remote['status'] = self::STATUS_IN_PROGRESS;
            $remoteTracker->update($x12_remote);

            // Upload the file
            try {
                $api->uploadClaimFile($claim_file_contents, $x12_remoteFilename);
            } catch (ClaimRevApiException) {
                $x12_remote['status'] = self::STATUS_UPLOAD_ERRROR;
                $x12_remote['messages'] = 'Could not upload file.';
                $remoteTracker->update($x12_remote);
                continue;
            }

            // Change status to success
            $x12_remote['status'] = self::STATUS_SUCCESS;
            $remoteTracker->update($x12_remote);
        }
    }
}
