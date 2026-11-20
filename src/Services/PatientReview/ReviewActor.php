<?php

/**
 * The patient, staff user or system process behind a submission or a state change.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

final readonly class ReviewActor
{
    /**
     * @param ?int $id pid for a patient, users.id for a user, null for the system
     * @param string $name the login name recorded in the audit log
     */
    private function __construct(
        public ReviewActorType $type,
        public ?int $id,
        public string $name,
    ) {
    }

    public static function patient(int $pid, string $portalUsername): self
    {
        if ($pid <= 0) {
            throw new \DomainException('Patient id must be positive');
        }
        return new self(ReviewActorType::Patient, $pid, $portalUsername);
    }

    public static function user(int $userId, string $username): self
    {
        if ($userId <= 0) {
            throw new \DomainException('User id must be positive');
        }
        return new self(ReviewActorType::User, $userId, $username);
    }

    public static function system(string $name = 'system'): self
    {
        return new self(ReviewActorType::System, null, $name);
    }
}
