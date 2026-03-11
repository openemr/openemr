<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

/**
 * (inferred from ApiResponseLoggerListener)
 * @phpstan-type ApiData array{
 *   user_id: int,
 *   patient_id: int,
 *   method: string,
 *   request: string,
 *   request_url: string,
 *   request_body: string,
 *   response: string,
 * }
 */
readonly class Event
{
    /**
     * @param ?ApiData $api
     */
    public function __construct(
        public string $current_datetime,
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
        public ?array $api,
    ) {
    }
}
