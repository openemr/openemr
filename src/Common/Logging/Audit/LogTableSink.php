<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

use OpenEMR\Common\Database\QueryUtils;

class LogTableSink
{
    public function record(Event $event): bool
    {
        // 1. insert entry into log table
        $logSql = <<<SQL
        INSERT INTO `log` (
            `date`,
            `event`,
            `category`,
            `user`,
            `groupname`,
            `comments`,
            `user_notes`,
            `patient_id`,
            `success`,
            `crt_user`,
            `log_from`,
            `menu_item_id`,
            `ccda_doc_id`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        SQL;
        $params = [
            $event->current_datetime,
            $event->event,
            $event->category,
            $event->user,
            $event->group,
            $event->comments,
            $event->user_notes,
            $event->patientId,
            $event->success,
            $event->SSL_CLIENT_S_DN_CN,
            $event->logFrom,
            $event->menuItemId,
            $event->ccdaDocId,
        ];

        $lastLogId = QueryUtils::sqlInsert($logSql, $params);
        $checksum = hash('sha3-512', implode('', $params));

        if ($event->api === null) {
            $checksumGenerateApi = '';
        } else {
            $api = $event->api;
            //...
            // api log
            $ipAddress = collectIpAddresses()['ip_string'];
            $apiLogEntry = [
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
            $checksumGenerateApi = hash('sha3-512', implode('', $apiLogEntry));
        }

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
            $encrypt,
            $checksum,
            $checksumGenerateApi,
            '4',
        ];


        // 2. insert associated entry (in addition to calculating and storing applicable checksums) into log_comment_encrypt
        $last_log_id = QueryUtils::getLastInsertId();
        $checksumGenerate = hash('sha3-512', implode('', $logEntry));
        sqlInsertClean_audit(
            "INSERT INTO `log_comment_encrypt` (`log_id`, `encrypt`, `checksum`, `checksum_api`, `version`) VALUES (?, ?, ?, ?, '4')",
            [
                $last_log_id,
                $encrypt,
                $checksumGenerate,
                $checksumGenerateApi
            ]
        );

    }
}
