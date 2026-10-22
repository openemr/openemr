<?php

/**
 * Puts a stored availability rule into words for the screen.
 *
 * "Everywhere", or its limits by name -- "Facility: Main Clinic; Visit category: Well
 * Child" -- and who may see what it offers. Names come from the pick lists; an id the lists
 * no longer carry (a deactivated provider, a removed app) is shown as "#id" so the rule stays
 * identifiable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability;

use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailabilityController;
use OpenEMR\Services\Questionnaire\AssignmentRecord;

final readonly class RuleDescriber
{
    /**
     * @param array<string, string> $surfaceLabels surface value => label
     * @param array<int, string> $facilities
     * @param array<int, string> $providers
     * @param array<int, string> $visitCategories
     * @param array<string, string> $smartApps client id => app name
     * @param array<string, string> $permissionLabels "section|level" => "Section: Level"
     */
    public function __construct(
        private array $surfaceLabels,
        private array $facilities,
        private array $providers,
        private array $visitCategories,
        private array $smartApps,
        private array $permissionLabels,
    ) {
    }

    /**
     * @param 'facility'|'provider'|'visit_category_id'|'client_id' $column
     */
    public static function scopeLabel(string $column): string
    {
        return match ($column) {
            'facility' => xl('Facility'),
            'provider' => xl('Provider'),
            'visit_category_id' => xl('Visit category'),
            'client_id' => xl('SMART app'),
        };
    }

    public function row(AssignmentRecord $rule, ?int $editingId, ?string $categoryLabel): RuleRow
    {
        $editUrl = $rule->resourceId !== null
            ? QuestionnaireAvailabilityController::PAGE_URL . '?q=' . $rule->resourceId . '&edit=' . $rule->id . '#limits'
            : QuestionnaireAvailabilityController::PAGE_URL . '?edit=' . $rule->id . '#category-rules';

        return new RuleRow(
            id: $rule->id,
            surfaceLabel: $this->surfaceLabels[$rule->surface->value] ?? $rule->surface->value,
            where: $this->where($rule),
            permission: $this->permission($rule),
            active: $rule->active,
            editUrl: $editUrl,
            highlighted: $editingId === $rule->id,
            categoryLabel: $categoryLabel,
        );
    }

    public function where(AssignmentRecord $rule): string
    {
        $parts = [];
        if ($rule->facility !== null) {
            $parts[] = self::scopeLabel('facility') . ': ' . self::nameOrId($this->facilities[$rule->facility] ?? null, $rule->facility);
        }
        if ($rule->provider !== null) {
            $parts[] = self::scopeLabel('provider') . ': ' . self::nameOrId($this->providers[$rule->provider] ?? null, $rule->provider);
        }
        if ($rule->visitCategoryId !== null) {
            $parts[] = self::scopeLabel('visit_category_id') . ': '
                . self::nameOrId($this->visitCategories[$rule->visitCategoryId] ?? null, $rule->visitCategoryId);
        }
        if ($rule->clientId !== null && $rule->clientId !== '') {
            $parts[] = self::scopeLabel('client_id') . ': '
                . ($this->smartApps[$rule->clientId] ?? $rule->clientId . ' ' . xl('(inactive or removed)'));
        }

        return $parts === [] ? xl('Everywhere') : implode('; ', $parts);
    }

    public function permission(AssignmentRecord $rule): string
    {
        if ($rule->aclSection !== null && $rule->aclLevel !== null) {
            return $this->permissionLabel($rule->aclSection, $rule->aclLevel);
        }
        [$section, $level] = $rule->surface->defaultAcl();

        return sprintf(xl('Default (%s)'), $this->permissionLabel($section, $level));
    }

    private function permissionLabel(string $section, string $level): string
    {
        return $this->permissionLabels[$section . '|' . $level] ?? $section . '/' . $level;
    }

    private static function nameOrId(?string $name, int $id): string
    {
        return $name !== null && $name !== '' ? $name : '#' . $id;
    }
}
