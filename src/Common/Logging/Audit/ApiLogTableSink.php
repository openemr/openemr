<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

class ApiLogTableSink
{
    public function record(Event $event): bool
    {
        // 3. if api log entry, then insert insert associated entry into api_log
        if ($event->api === null) {
            return false;
        }

        sqlInsertClean_audit("INSERT INTO `api_log` (`log_id`, `user_id`, `patient_id`, `ip_address`, `method`, `request`, `request_url`, `request_body`, `response`, `created_time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $apiLogEntry);
    }
}
