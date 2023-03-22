<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    namespace OpenEMR\Modules\ClaimRevConnector;

    use OpenEMR\Services\BaseService;
    use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
    use OpenEMR\Billing\BillingProcessor\X12RemoteTracker;

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

    public static function sendWaitingFiles()
    {
        $remoteTracker = new X12RemoteTracker();
        $x12_remotes = $remoteTracker->fetchByStatus(self::STATUS_WAITING);
        $x12_remote['messages'] = [];

        $token = ClaimRevApi::GetAccessToken();

        foreach ($x12_remotes as $x12_remote) {
            if (false === $token) {
                $x12_remote['status'] = self::STATUS_LOGIN_ERROR;
                $x12_remote['messages'] = "Invalid Username or Password.";
                $remoteTracker->update($x12_remote);
                continue;
            }

            $x12_remoteFilename = $x12_remote['x12_filename'];
            // Make sure local claim file exists and can we have permission to read it
            $claim_file = $x12_remote['x12_sftp_local_dir'] . $x12_remoteFilename;
            if (!file_exists($claim_file)) {
                $claim_file = $GLOBALS['OE_SITE_DIR'] . "/documents/edi/" . $x12_remoteFilename;
            }

            $claim_file_contents = file_get_contents($claim_file);
            if (false === $claim_file_contents) {
                $x12_remote['status'] = self::STATUS_CLAIM_FILE_ERROR;
                $x12_remote['messages'] = "Could not open local claim file: `$claim_file`";
                $remoteTracker->update($x12_remote);
                continue;
            }

            // Change status from waiting to in-progress
            $x12_remote['status'] = self::STATUS_IN_PROGRESS;
            $remoteTracker->update($x12_remote);

            // Upload the file
            if (false === ClaimRevApi::uploadClaimFile($claim_file_contents, $x12_remoteFilename, $token)) {
                $x12_remote['status'] = self::STATUS_UPLOAD_ERRROR;
                $x12_remote['messages'] = "Could not upload file.";
                $remoteTracker->update($x12_remote);
                continue;
            }

            // Change status from waiting to in-progress
            $x12_remote['status'] = self::STATUS_SUCCESS;
            $remoteTracker->update($x12_remote);
        }
    }
}
