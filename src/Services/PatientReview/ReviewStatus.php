<?php

/**
 * The single state of a review request and the transitions allowed between states.
 *
 * Replaces the four free-text columns of onsite_portal_activity (require_audit, pending_action,
 * action_taken, status), which together encoded one state with no defined transitions.
 *
 *   pending ---------> approved ---> completed
 *      |  \               |
 *      |   +--> denied    +--> cancelled
 *      +------> cancelled
 *      +------> completed      (legacy workflows close a request in one step)
 *   awaiting_payment --> completed | cancelled
 *
 * Stored in patient_review_request.status, so the values are a persistence contract.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

enum ReviewStatus: string
{
    /** Waiting for staff to review. */
    case Pending = 'pending';
    /** Staff asked the patient to pay; waiting for the patient. */
    case AwaitingPayment = 'awaiting_payment';
    /** Staff approved; the change is being applied. */
    case Approved = 'approved';
    case Denied = 'denied';
    /** Withdrawn or superseded before it was decided. */
    case Cancelled = 'cancelled';
    /** Decided and applied. */
    case Completed = 'completed';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Denied, self::Cancelled, self::Completed],
            self::AwaitingPayment => [self::Completed, self::Cancelled],
            self::Approved => [self::Completed, self::Cancelled],
            self::Denied, self::Cancelled, self::Completed => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    /**
     * Open requests are the ones still waiting on someone. Only an open request may hold a
     * secret (payment card data), and there is at most one visible per patient and type.
     */
    public function isOpen(): bool
    {
        return match ($this) {
            self::Pending, self::AwaitingPayment => true,
            self::Approved, self::Denied, self::Cancelled, self::Completed => false,
        };
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }
}
