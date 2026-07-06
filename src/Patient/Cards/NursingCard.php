<?php

/**
 * Nursing Card
 *
 * A class representing the Nursing admission panel displayed on the patient dashboard.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    g0tazu
 * @copyright Copyright (c) 2026 g0tazu
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Patient\Cards;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Services\Globals\UserSettingsService;

class NursingCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/partials/nursing.html.twig';
    private const CARD_ID = 'nursing_admission';

    public function __construct(int $pid)
    {
        parent::__construct($this->buildOpts($pid));
    }

    /** @return array<string,mixed> */
    private function buildOpts(int $pid): array
    {
        $admission = $this->getActiveAdmission($pid);
        $webroot = OEGlobalsBag::getInstance()->getString('webroot');

        return [
            'acl' => ['patients', 'med'],
            'initiallyCollapsed' => (UserSettingsService::getUserSetting(self::CARD_ID . '_expand') == 0),
            'add' => false,
            'edit' => false,
            'collapse' => true,
            'templateFile' => self::TEMPLATE_FILE,
            'identifier' => self::CARD_ID,
            'title' => xl('Nursing'),
            'templateVariables' => [
                'admission' => $admission,
                'webroot'   => $webroot,
                'pid'       => $pid,
            ],
        ];
    }

    /** @return array<string,mixed>|null */
    private function getActiveAdmission(int $pid): ?array
    {
        $catRow = QueryUtils::querySingleRow(
            "SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_catname = ? LIMIT 1",
            ['Inpatient']
        );
        if (!$catRow || ($catRow['pc_catid'] ?? '') === '') {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            "SELECT fe.id AS encounter_id, fe.encounter, fe.date AS admission_date,
                    fe.nro_registro, fe.departamento, fe.servicio, fe.cuarto, fe.cama
             FROM form_encounter AS fe
             WHERE fe.pid = ? AND fe.pc_catid = ? AND fe.date_end IS NULL
             ORDER BY fe.date DESC
             LIMIT 1",
            [$pid, (int)((string)($catRow['pc_catid'] ?? 0))]
        );

        return $row ?: null;
    }
}
