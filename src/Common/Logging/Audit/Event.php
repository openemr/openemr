<?php

/**
 * Data transfer object for auditable events
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
        public ?string $user,
        public ?string $group,
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
