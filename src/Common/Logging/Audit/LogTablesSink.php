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
            'user' => $event->user ?? '',
            'groupname' => $event->group ?? '',
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
            $apiLogData = [
                'log_id' => $lastLogId,
                'user_id' => $api['user_id'],
                'patient_id' => $api['patient_id'],
                'ip_address' => $ipAddress,
                'method' => $api['method'],
                'request' => $api['request'],
                'request_url' => $api['request_url'],
                'request_body' => $api['request_body'],
                'response' => $api['response'],
                'created_time' => $event->current_datetime,
            ];
            $checksumGenerateApi = hash('sha3-512', implode('', array_values($apiLogData)));
        }

        // 2. insert associated entry (in addition to calculating and storing applicable checksums) into log_comment_encrypt
        $logCommentData = [
            'log_id' => $lastLogId,
            'encrypt' => $this->shouldEncrypt ? 'Yes' : 'No', // DB is a Yes/No enum instead of bool :(
            'checksum' => $checksum,
            'checksum_api' => $checksumGenerateApi,
            'version' => '4',
        ];
        $this->conn->insert('log_comment_encrypt', $logCommentData);

        // 3. if api log entry, then insert insert associated entry into api_log
        if ($api !== null) {
            $this->conn->insert('api_log', $apiLogData);
        }

        return true;
    }
}
