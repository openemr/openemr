<?php

/**
 * Which collapsible page sections the current user left collapsed.
 *
 * Uses the same per-user settings as the patient dashboard cards: library/ajax/user_settings.php
 * stores "0" under a key ending in _expand when a section is collapsed and "1" when it is
 * opened again. A section with no setting starts open.
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
     * @param list<string> $sections setting keys, each ending in _expand
     */
    public static function forCurrentUser(array $sections): self
    {
        $collapsed = [];
        foreach ($sections as $section) {
            if (UserSettingsService::getUserSetting($section) === self::COLLAPSED) {
                $collapsed[] = $section;
            }
        }

        return new self($collapsed);
    }

    public function isCollapsed(string $section): bool
    {
        return in_array($section, $this->collapsed, true);
    }
}
