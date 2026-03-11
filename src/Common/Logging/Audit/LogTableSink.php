<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

class LogTableSink
{
    public function record(Event $event): bool
    {
        sqlInsertClean_audit("insert into `log` (`date`, `event`, `category`, `user`, `groupname`, `comments`, `user_notes`, `patient_id`, `success`, `crt_user`, `log_from`, `menu_item_id`, `ccda_doc_id`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $logEntry);
    }
}
