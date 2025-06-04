<?php

/**
 * Model that provides tracking information for a run of claim processing using
 * the billing manager. Each run is saved as an entry in the billing_tracker_batch
 * table.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\BaseService;
use phpseclib3\Net\SFTP;

class X12RemoteTracker extends BaseService
{
    const STATUS_WAITING = 'waiting';
    const STATUS_PARAMETER_ERROR = 'parameter-error';
    const STATUS_CLAIM_FILE_ERROR = 'claim-file-error';
    const STATUS_LOGIN_ERROR = 'login-error';
    const STATUS_CHDIR_ERROR = 'chdir-error';
    const STATUS_IN_PROGRESS = 'in-progress';
    const STATUS_UPLOAD_ERRROR = 'upload-error';
    const STATUS_SUCCESS = 'success';

    const TABLE_NAME = 'x12_remote_tracker';

    protected static $x12_partner_field_keys = [
        'x12_sftp_host' => 'X12 SFTP Host',
        'x12_sftp_port' => 'X12 SFTP Port',
        'x12_sftp_login' => 'X12 SFTP Login',
        'x12_sftp_pass' => 'X12 SFTP Password',
        'x12_sftp_remote_dir' => 'X12 SFTP Remote Dir',
        'x12_sftp_local_dir' => 'X12 SFTP Local Dir',
    ];

    const SELECT = "SELECT R.id, R.x12_filename, R.status, R.messages, R.claims, R.created_at, R.updated_at,
       P.name, P.id AS x12_partner_id, P.x12_sftp_host, P.x12_sftp_port, P.x12_sftp_login, P.x12_sftp_pass,
       P.x12_sftp_remote_dir, P.x12_sftp_local_dir FROM x12_remote_tracker R";

    protected $validationMessages = [];

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public static function sftpSendWaitingFiles()
    {
        $remoteTracker = new X12RemoteTracker();
        $x12_remotes = $remoteTracker->fetchByStatus(self::STATUS_WAITING);
        $cryptoGen = new CryptoGen();
        foreach ($x12_remotes as $x12_remote) {
            // Make sure required parameters are filled in on the X12 partner form, otherwise, log a message
            if (false === $remoteTracker->validateSFTPCredentials($x12_remote)) {
                // there was a problem, get messages, log them and continue
                $x12_remote['status'] = self::STATUS_PARAMETER_ERROR;
                $x12_remote['messages'] = array_merge(
                    ($x12_remote['messages'] ?? []),
                    $remoteTracker->validationMessages
                );
                $remoteTracker->update($x12_remote);
                continue;
            }

            // Make sure local claim file exists and can we have permission to read it
            // We try both the SFTP directory and the edi root directry
            $claim_file = $x12_remote['x12_sftp_local_dir'] . $x12_remote['x12_filename'];
            if (!file_exists($claim_file)) {
                $claim_file = $GLOBALS['OE_SITE_DIR'] . "/documents/edi/" . $x12_remote['x12_filename'];
            }

            $claim_file_contents = file_get_contents($claim_file);
            if (false === $claim_file_contents) {
                $x12_remote['status'] = self::STATUS_CLAIM_FILE_ERROR;
                $x12_remote['messages'][] = "Could not open local claim file: `$claim_file`";
                $remoteTracker->update($x12_remote);
                continue;
            }

            // Attempt to login
            $sftp = new SFTP($x12_remote['x12_sftp_host'], $x12_remote['x12_sftp_port']);
            $decrypted_password = $cryptoGen->decryptStandard($x12_remote['x12_sftp_pass']);
            if (false === $sftp->login($x12_remote['x12_sftp_login'], $decrypted_password)) {
                $x12_remote['status'] = self::STATUS_LOGIN_ERROR;
                $x12_remote['messages'][] = "Invalid Username or Password.";
                $x12_remote['messages'] = array_merge($x12_remote['messages'], $sftp->getSFTPErrors());
                $remoteTracker->update($x12_remote);
                continue;
            }

            if (false === $sftp->chdir($x12_remote['x12_sftp_remote_dir'])) {
                $x12_remote['status'] = self::STATUS_CHDIR_ERROR;
                $x12_remote['messages'][] = "Could not change to SFTP remote DIR.";
                $x12_remote['messages'] = array_merge($x12_remote['messages'], $sftp->getSFTPErrors());
                $remoteTracker->update($x12_remote);
                continue;
            }

            // Change status from waiting to in-progress
            $x12_remote['status'] = self::STATUS_IN_PROGRESS;
            $remoteTracker->update($x12_remote);

            // Upload the file
            if (false === $sftp->put($x12_remote['x12_filename'], $claim_file_contents)) {
                $x12_remote['status'] = self::STATUS_UPLOAD_ERRROR;
                $x12_remote['messages'][] = "Could not upload file.";
                $x12_remote['messages'] = array_merge($x12_remote['messages'], $sftp->getSFTPErrors());
                $remoteTracker->update($x12_remote);
            }

            // Change status from waiting to in-progress
            $x12_remote['status'] = self::STATUS_SUCCESS;
            $remoteTracker->update($x12_remote);

            // Disconnect from the remote server
            $sftp->disconnect();
        }
    }

    protected function validateSFTPCredentials($credentials)
    {
        $this->validationMessages = [];
        $valid = true;
        foreach (self::$x12_partner_field_keys as $key => $label) {
            if (empty($credentials[$key])) {
                $this->validationMessages[] = "`$label` is required";
                $valid = false;
            }
        }
        return $valid;
    }

    public static function create($fields)
    {
        $fields['created_at'] = date('Y-m-d h:i:s');
        $fields['updated_at'] = date('Y-m-d h:i:s');
        $remoteTracker = new X12RemoteTracker();
        return $remoteTracker->insert($fields);
    }

    public function insert($fields)
    {
        $setQueryPart = $this->buildInsertColumns($this->onlyRealFields($fields));
        $sql = " INSERT INTO x12_remote_tracker SET ";
        $sql .= $setQueryPart['set'];

        $results = sqlInsert(
            $sql,
            $setQueryPart['bind']
        );

        return $results;
    }

    public function update($fields)
    {
        if (is_array($fields['messages'])) {
            $fields['messages'] = json_encode($fields['messages']);
        }
        $fields['updated_at'] = date('Y-m-d h:i:s');
        $query = $this->buildUpdateColumns($this->onlyRealFields($fields));
        $sql = "UPDATE x12_remote_tracker SET ";
        $sql .= $query['set'];
        $sql .= "WHERE id = ?";
        array_push($query['bind'], $fields['id']);
        $results = sqlStatement($sql, $query['bind']);
        return $results;
    }

    protected function onlyRealFields($passed_in)
    {
        $realFields = [];
        foreach ($passed_in as $key => $value) {
            if (in_array($key, $this->getFields())) {
                $realFields[$key] = $value;
            }
        }
        return $realFields;
    }

    /**
     * Get the remote tracking entries by their status with the newest first
     *
     * @param string $status
     * @return array
     */
    public function fetchByStatus($status = self::STATUS_WAITING)
    {
        $waiting = self::selectHelper(self::SELECT, [
            'join' => "JOIN x12_partners P ON P.id = R.x12_partner_id",
            'where' => "WHERE `status` = ?",
            'order' => 'ORDER BY R.created_at DESC',
            'data' => [$status]
        ]);

        return $waiting;
    }

    public function fetchAll()
    {
        $all = self::selectHelper(self::SELECT, [
            'join' => "JOIN x12_partners P ON P.id = R.x12_partner_id",
            'order' => 'ORDER BY R.updated_at DESC'
        ]);

        return $all;
    }
}
