<?php

/**
 * Authorization policy for attributing a clinical act to a practitioner.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Uuid\UuidRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Decides whether the authenticated user may record a clinical act as performed by a given
 * practitioner.
 *
 * Several FHIR write paths take a Practitioner reference out of the payload and store it as the
 * performer of the act -- Encounter.participant, Appointment.participant, Immunization.performer,
 * MedicationRequest.requester, ServiceRequest.requester. Those columns feed audit trails, billing
 * and clinical accountability, so a caller must not be able to attribute their own writes to a
 * colleague simply by naming them in the body. A caller may attribute to themselves; attributing
 * to anyone else requires the admin/users ACL.
 *
 * Violations throw rather than silently dropping the attribution: a silent drop would report
 * success while the record is attributed to whatever default the downstream service picks, which
 * is worse than an outright rejection because nobody sees it.
 */
final readonly class PractitionerAttributionPolicy
{
    private string $authUserId;

    private bool $canAttributeToAnyone;

    public function __construct(?SessionInterface $session)
    {
        $authUserRaw = $session?->get('authUser');
        $authUser = is_string($authUserRaw) ? $authUserRaw : '';
        $authUserIdRaw = $session?->get('authUserID');

        $this->authUserId = is_scalar($authUserIdRaw) ? (string) $authUserIdRaw : '';
        $this->canAttributeToAnyone = $authUser !== ''
            && AclMain::aclCheckCore('admin', 'users', $authUser) !== false;
    }

    /**
     * True when the caller holds admin/users and may therefore name any practitioner.
     */
    public function canAttributeToAnyone(): bool
    {
        return $this->canAttributeToAnyone;
    }

    /**
     * Whether the caller may record an act as performed by this practitioner.
     *
     * The non-throwing form, for the one caller (Appointment) whose attribution field is optional
     * and which therefore drops a disallowed reference instead of failing the whole write.
     *
     * @param int|string $practitionerId The resolved `users`.`id`
     */
    public function mayAttributeTo(int|string $practitionerId): bool
    {
        return $this->canAttributeToAnyone
            || ($this->authUserId !== '' && (string) $practitionerId === $this->authUserId);
    }

    /**
     * Rejects an attribution to a practitioner other than the authenticated user.
     *
     * @param int|string $practitionerId The resolved `users`.`id`
     * @param string $fhirLabel The payload element being attributed, for the error message
     * @throws \InvalidArgumentException when the caller may not attribute to this practitioner
     */
    public function assertMayAttributeTo(int|string $practitionerId, string $fhirLabel): void
    {
        if ($this->mayAttributeTo($practitionerId)) {
            return;
        }
        throw new \InvalidArgumentException(
            $fhirLabel . ' attribution to another practitioner requires admin/users'
        );
    }

    /**
     * Resolves a Practitioner uuid to a `users`.`id` and applies the policy to it.
     *
     * @param callable(string): mixed $resolver Maps uuid bytes to a users.id; anything non-numeric
     *     (false, null) is treated as unresolvable
     * @throws \InvalidArgumentException when the uuid is malformed, unresolvable, or not permitted
     */
    public function resolveAndAssert(string $practitionerUuid, string $fhirLabel, callable $resolver): int
    {
        if (!UuidRegistry::isValidStringUUID($practitionerUuid)) {
            throw new \InvalidArgumentException($fhirLabel . ' reference is not a valid uuid');
        }
        $practitionerId = $resolver(UuidRegistry::uuidToBytes($practitionerUuid));
        if (!is_numeric($practitionerId)) {
            throw new \InvalidArgumentException($fhirLabel . ' reference could not be resolved');
        }
        // Narrowed to int before the policy check: is_numeric() admits floats and numeric
        // strings, and the id compared against the session must be the same shape that gets
        // stored.
        $resolvedId = (int) $practitionerId;
        $this->assertMayAttributeTo($resolvedId, $fhirLabel);

        return $resolvedId;
    }
}
