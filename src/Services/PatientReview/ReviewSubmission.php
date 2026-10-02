<?php

/**
 * What is needed to create a review request.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

final readonly class ReviewSubmission
{
    /**
     * @param array<array-key, mixed> $payload the submitted data; stored as JSON
     * @param ?string $clientId oauth_clients.client_id, required when the source is an API app
     * @param ?string $targetTable the table the request applies to, when known
     * @param ?string $targetId the row or uuid in that table
     */
    public function __construct(
        public int $pid,
        public ReviewType $type,
        public string $summary,
        public array $payload,
        public ReviewSource $source = ReviewSource::Portal,
        public ?string $clientId = null,
        public ?string $targetTable = null,
        public ?string $targetId = null,
    ) {
        if ($pid <= 0) {
            throw new \DomainException('Patient id must be positive');
        }
        if ($source === ReviewSource::Api && ($clientId === null || $clientId === '')) {
            throw new \DomainException('An API submission must name its client');
        }
        if ($source === ReviewSource::Portal && $clientId !== null) {
            throw new \DomainException('A portal submission has no client');
        }
    }
}
