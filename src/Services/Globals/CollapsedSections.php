<?php

/**
 * Which collapsible page sections the current user left collapsed.
 *
 * Uses the same per-user settings as the patient dashboard cards: library/ajax/user_settings.php
 * stores "0" under a key ending in _expand when a section is collapsed and "1" when it is
 * opened again. A section the user never set starts open.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Globals;

final readonly class CollapsedSections
{
    private const COLLAPSED = '0';

    /**
     * @param list<string> $collapsed setting keys of the sections that start collapsed
     */
    public function __construct(private array $collapsed = [])
    {
    }

    /**
     * Read the current user's own choices.
     *
     * Only the user's own row counts. Saving a setting also stores the first user's choice
     * as a default for everyone, and taking that would fold sections, such as the import
     * controls, for users who never chose to.
     *
     * @param list<string> $sections setting keys, each ending in _expand
     */
    public static function forCurrentUser(array $sections): self
    {
        $user = filter_var(UserSettingsService::effectiveUser(null), FILTER_VALIDATE_INT);
        if ($user === false) {
            return new self();
        }
        $collapsed = [];
        foreach ($sections as $section) {
            if (UserSettingsService::getUserSetting($section, $user, $user) === self::COLLAPSED) {
                $collapsed[] = $section;
            }
        }

        return new self($collapsed);
    }

    /**
     * The setting key for a group of questionnaires in one category.
     *
     * A hash of the whole list option id: the choice survives a change of language, and ids
     * that differ only in punctuation, such as a-b and a_b, keep separate states.
     */
    public static function categoryKey(string $category): string
    {
        return 'questionnaire_category_'
            . ($category !== '' ? hash('xxh3', $category) : 'unassigned')
            . '_expand';
    }

    /**
     * Whether the section with this setting key starts collapsed.
     */
    public function isCollapsed(string $section): bool
    {
        return in_array($section, $this->collapsed, true);
    }
}
