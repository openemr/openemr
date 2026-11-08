<?php

/**
 * Care Plan Dashboard Card
 *
 * Renders the patient's most recent care plan form on the patient dashboard.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Patient\Cards;

use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Services\Forms\CarePlanFormService;
use OpenEMR\Services\FormService;
use OpenEMR\Services\Globals\UserSettingsService;

class CarePlanViewCard extends CardModel
{
    public const CARD_ID = 'card_care_plan';

    private const TEMPLATE_FILE = 'patient/card/care_plan.html.twig';

    public function __construct(
        private readonly int $pid,
        private readonly CarePlanFormService $carePlanFormService,
        private readonly FormService $formService,
    ) {
        parent::__construct([
            'acl' => ['patients', 'med'],
            'initiallyCollapsed' => $this->isInitiallyCollapsed(),
            'add' => false,
            'edit' => false,
            'collapse' => true,
            'templateFile' => self::TEMPLATE_FILE,
            'identifier' => self::CARD_ID,
            'title' => xl('Care Plan'),
            'templateVariables' => [],
        ]);
    }

    public function getTemplateFile(): string
    {
        return self::TEMPLATE_FILE;
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Whether the current user may see the card at all.
     *
     * Delegates to the same ACO the care plan encounter form itself checks, so revoking
     * access to the form also hides the dashboard summary of it.
     */
    public function isVisible(): bool
    {
        return $this->formService->hasFormPermission(CarePlanFormService::FORM_DIR);
    }

    /**
     * @return array<string, mixed>
     */
    public function getTemplateVariables(): array
    {
        $carePlan = ($this->pid > 0 && $this->isVisible())
            ? $this->carePlanFormService->getMostRecentCarePlanForPid($this->pid)
            : null;

        return [
            'id' => self::CARD_ID,
            'title' => $this->getTitle(),
            'initiallyCollapsed' => $this->isInitiallyCollapsed(),
            'pid' => $this->pid,
            'rows' => $carePlan['rows'] ?? [],
            'mostRecentDate' => $carePlan['date'] ?? null,
            'encounter' => $carePlan['encounter'] ?? null,
        ];
    }

    public function getTitle(): string
    {
        return xl('Care Plan');
    }

    public function getIdentifier(): string
    {
        return self::CARD_ID;
    }

    public function isInitiallyCollapsed(): bool
    {
        return (int) UserSettingsService::getUserSetting(self::CARD_ID) === 0;
    }

    public function canAdd(): bool
    {
        return false;
    }

    public function canEdit(): bool
    {
        return false;
    }
}
