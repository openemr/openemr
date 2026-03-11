<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

use Doctrine\DBAL\Connection;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Database\QueryUtils;

class LogTablesSink
{
    /**
     * CRITICAL: The connection must be separate from the one used for other
     * database operations. If it's not, autoincrement IDs will start
     * cross-talking during SQL events being logged, since it's stateful across
     * the connection rather than the query.
     *
     * If $crypto is set, the api body data will be stored encrypted and
     * marked as such. If null, they will be blanked out.
     */
    public function __construct(
        private Connection $conn,
        private CryptoInterface $crypto,
        private bool $shouldEncrypt,
    ) {
    }

    public function record(Event $event): bool
    {
        $api = $event->api;
        if ($this->shouldEncrypt) {
            $comments = $this->crypto->encryptStandard($event->comments);
            if ($api !== null) {
                $api['request_url'] = ($api['request_url'] === '') ? '' : $this->crypto->encryptStandard($api['request_url']);
                $api['request_body'] = ($api['request_body'] === '') ? '' : $this->crypto->encryptStandard($api['request_body']);
                $api['response'] = ($api['response'] === '') ? '' : $this->crypto->encryptStandard($api['response']);
            }
        } else {
            // Since storing binary elements (uuid), need to base64 to not jarble them and to ensure the auditing hashing works
            $comments = base64_encode($event->comments);
            // Should this blank out the api fields? Previous behavior was that
            // it did not.
        }


        // 1. insert entry into log table
        $logData = [
            'date' => $event->current_datetime,
            'event' => $event->event,
            'category' => $event->category,
            'user' => $event->user,
            'groupname' => $event->group,
            'comments' => $comments,
            'user_notes' => $event->user_notes,
            'patient_id' => $event->patientId,
            'success' => $event->success,
            'crt_user' => $event->SSL_CLIENT_S_DN_CN,
            'log_from' => $event->logFrom,
            'menu_item_id' => $event->menuItemId,
            'ccda_doc_id' => $event->ccdaDocId,
        ];
        $this->conn->insert('log', $logData);
        $lastLogId = $this->conn->lastInsertId();

        $checksum = hash('sha3-512', implode('', array_values($logData)));

        if ($api === null) {
            $checksumGenerateApi = '';
        } else {
            //...
            // api log
            $ipAddress = collectIpAddresses()['ip_string'];
            $apiLogParams = [
                $lastLogId,
                $api['user_id'],
                $api['patient_id'],
                $ipAddress,
                $api['method'],
                $api['request'],
                $api['request_url'],
                $api['request_body'],
                $api['response'],
                $event->current_datetime,
            ];
            $checksumGenerateApi = hash('sha3-512', implode('', $apiLogParams));
        }

        // 2. insert associated entry (in addition to calculating and storing applicable checksums) into log_comment_encrypt
        $logCommentSql = <<<SQL
        INSERT INTO `log_comment_encrypt` (
            `log_id`,
            `encrypt`,
            `checksum`,
            `checksum_api`,
            `version`
        ) VALUES (?, ?, ?, ?, ?)
        SQL;
        $logCommentParams = [
            $lastLogId,
            $this->shouldEncrypt ? 'Yes' : 'No', // DB is a Yes/No enum instead of bool :(
            $checksum,
            $checksumGenerateApi,
            '4',
        ];
        QueryUtils::sqlInsert($logCommentSql, $logCommentParams);

        // 3. if api log entry, then insert insert associated entry into api_log
        if ($event->api !== null) {
            $apiLogSql = <<<SQL
            INSERT INTO `api_log` (
                `log_id`,
                `user_id`,
                `patient_id`,
                `ip_address`,
                `method`,
                `request`,
                `request_url`,
                `request_body`,
                `response`,
                `created_time`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            SQL;
            QueryUtils::sqlInsert($apiLogSql, $apiLogParams);
        }

        return true;
    }
}
