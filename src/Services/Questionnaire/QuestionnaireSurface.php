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
}
