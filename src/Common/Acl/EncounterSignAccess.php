<?php

/**
 * Who may sign an encounter.
 *
 * Signing is an encounter authorization: "Authorize - any" (encounters / auth_a) allows any encounter,
 * "Authorize - my encounters" (encounters / auth) only an encounter the user created or is the provider of.
 * The caller reads the ACL and the encounter's creator and provider; this class only decides, so the rule
 * can be tested without a database or a session.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Acl;

final class EncounterSignAccess
{
    /**
     * @param bool  $hasAuthAny   the user holds encounters / auth_a
     * @param bool  $hasAuthOwn   the user holds encounters / auth
     * @param mixed $encounterId  the encounter number from the request
     * @param mixed $creator      forms.user of the encounter's newpatient row (a username)
     * @param mixed $providerId   form_encounter.provider_id
     * @param mixed $authUser     the signed-in username
     * @param mixed $authUserId   the signed-in user's id
     */
    public static function allows(
        bool $hasAuthAny,
        bool $hasAuthOwn,
        mixed $encounterId,
        mixed $creator,
        mixed $providerId,
        mixed $authUser,
        mixed $authUserId
    ): bool {
        if ($hasAuthAny) {
            return true;
        }
        if (!$hasAuthOwn) {
            return false;
        }
        if (filter_var($encounterId, FILTER_VALIDATE_INT) === false) {
            return false;
        }
        if (is_string($creator) && $creator !== '' && $creator === $authUser) {
            return true;
        }
        $provider = filter_var($providerId, FILTER_VALIDATE_INT);
        return $provider !== false && $provider > 0 && $provider === filter_var($authUserId, FILTER_VALIDATE_INT);
    }
}
