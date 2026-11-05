<?php

/**
 * Who is calling portal/patient: a portal patient, a visitor registering for the portal, or a
 * staff user. portal/patient/_machine_config.php decides which, from the session, before
 * routing; staff need the patientportal/portal ACL to get this far.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Portal\Routing;

final readonly class PortalPrincipal
{
    private function __construct(
        public PrincipalKind $kind,
        public ?int $pid = null,
    ) {
    }

    /**
     * @param int $pid the session's patient
     */
    public static function portalPatient(int $pid): self
    {
        return new self(PrincipalKind::PortalPatient, $pid);
    }

    public static function registering(): self
    {
        return new self(PrincipalKind::Registering);
    }

    public static function staff(): self
    {
        return new self(PrincipalKind::Staff);
    }

    /**
     * The portal patient's pid when the caller is one, so their requests stay on their own
     * record; null for staff and registration.
     */
    public function patientPid(): ?int
    {
        return $this->kind === PrincipalKind::PortalPatient ? $this->pid : null;
    }
}
