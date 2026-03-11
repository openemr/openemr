<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

readonly class Event
{
    public function __construct(
        public $current_datetime,
        public $event,
        public $category,
        public $user,
        public $group,
        public $comments,
        public $user_notes,
        public $patientId,
        public $success,
        public $SSL_CLIENT_S_DN_CN,
        public $logFrom,
        public $menuItemId,
        public $ccdaDocId,
    ) {
    }
}
