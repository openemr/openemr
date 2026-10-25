<?php

/**
 * Surfaces a questionnaire can be offered on.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

/**
 * Backed because the value is persisted in `content_assignments`.`surface`.
 */
enum QuestionnaireSurface: string
{
    case Dashboard = 'dashboard';
    case Encounter = 'encounter';
    case Portal = 'portal';
    case Smart = 'smart';

    /**
     * Default ACL for a surface when an assignment does not name one.
     *
     * These reproduce the checks each surface performs today, so an assignment that
     * leaves `acl_section` null is exactly as permissive as current behavior.
     *
     * @return array{string, string}
     */
    public function defaultAcl(): array
    {
        return match ($this) {
            self::Dashboard, self::Smart => ['patients', 'med'],
            self::Encounter => ['encounters', 'notes'],
            self::Portal => ['patients', 'docs'],
        };
    }

    /**
     * The scope columns this surface's caller fills in, in AvailabilityContext order.
     *
     * A context member left null matches every rule, so a rule scoped on an axis its
     * surface never fills in is not narrowed at all. A visit-category rule on the
     * dashboard would show at every visit, which is the opposite of what the
     * administrator asked for. assign() refuses such a rule, and the screen disables
     * those fields.
     *
     *  - dashboard: the user's current facility; no encounter, so no provider or visit
     *  - encounter: facility, provider and visit category off the encounter row
     *  - smart: the user's current facility and the app being launched, from both the
     *    dashboard launch menu and the launch endpoint
     *  - portal: nothing yet; the portal does not read assignments
     *
     * @return list<'facility'|'provider'|'visit_category_id'|'client_id'>
     */
    public function supportedScopes(): array
    {
        return match ($this) {
            self::Dashboard => ['facility'],
            self::Encounter => ['facility', 'provider', 'visit_category_id'],
            self::Smart => ['facility', 'client_id'],
            self::Portal => [],
        };
    }
}
