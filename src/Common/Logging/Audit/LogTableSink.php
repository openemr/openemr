<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

class LogTableSink
{
    public function record(Event $event): bool
    {
        // 1. insert entry into log table
        sqlInsertClean_audit("insert into `log` (`date`, `event`, `category`, `user`, `groupname`, `comments`, `user_notes`, `patient_id`, `success`, `crt_user`, `log_from`, `menu_item_id`, `ccda_doc_id`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $logEntry);



        // 2. insert associated entry (in addition to calculating and storing applicable checksums) into log_comment_encrypt
        $last_log_id = QueryUtils::getLastInsertId();
        $checksumGenerate = hash('sha3-512', implode('', $logEntry));
        if (!empty($api)) {
            // api log
            $ipAddress = collectIpAddresses()['ip_string'];
            $apiLogEntry = [
                $last_log_id,
                $api['user_id'],
                $api['patient_id'],
                $ipAddress,
                $api['method'],
                $api['request'],
                $api['request_url'],
                $api['request_body'],
                $api['response'],
                $current_datetime
            ];
            $checksumGenerateApi = hash('sha3-512', implode('', $apiLogEntry));
        } else {
            $checksumGenerateApi = '';
        }
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
