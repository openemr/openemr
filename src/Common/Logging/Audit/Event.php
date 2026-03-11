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
        public string $event,
        public ?string $category,
        public string $user,
        public string $group,
        public string $comments,
        public string $user_notes,
        public ?int $patientId,
        public int $success,
        public string $SSL_CLIENT_S_DN_CN,
        public string $logFrom,
        public ?int $menuItemId,
        public ?int $ccdaDocId,
        public ?array $api,
    ) {
    }
}
