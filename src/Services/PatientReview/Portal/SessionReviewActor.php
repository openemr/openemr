<?php

/**
 * Who is acting on the review queue, read from the session of the request.
 *
 * The portal entry points run either in a patient's portal session or in a staff session
 * (the review screens open portal pages too). This reads which one it is at the boundary, so
 * the review queue is only ever handed a typed ReviewActor.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

use OpenEMR\Services\PatientReview\ReviewActor;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionReviewActor
{
    /**
     * A patient in a portal session, otherwise the signed-in staff user.
     *
     * @throws \DomainException when the session has neither
     */
    public static function from(SessionInterface $session): ReviewActor
    {
        if ($session->has('patient_portal_onsite_two')) {
            return self::patient($session);
        }

        return self::staff($session);
    }

    /**
     * @throws \DomainException when the session is not a portal patient session
     */
    public static function patient(SessionInterface $session): ReviewActor
    {
        $pid = self::positiveInt($session->get('pid'));
        if ($pid === null) {
            throw new \DomainException('The session has no portal patient');
        }
        $username = $session->get('portal_username');

        return ReviewActor::patient($pid, is_string($username) ? $username : '');
    }

    /**
     * @throws \DomainException when the session has no signed-in staff user
     */
    public static function staff(SessionInterface $session): ReviewActor
    {
        $userId = self::positiveInt($session->get('authUserID'));
        if ($userId === null) {
            throw new \DomainException('The session has no signed-in user');
        }
        $username = $session->get('authUser');

        return ReviewActor::user($userId, is_string($username) ? $username : '');
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }
        if (is_string($value) && preg_match('/^[1-9]\d*$/', $value) === 1) {
            return intval($value);
        }
        return null;
    }
}
