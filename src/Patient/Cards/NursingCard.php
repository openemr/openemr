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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NursingCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/partials/nursing.html.twig';
    private const CARD_ID = 'nursing_admission';

    public function __construct(int $pid, ?EventDispatcherInterface $dispatcher = null)
    {
        $opts = $this->buildOpts($pid);
        if ($dispatcher !== null) {
            $opts['dispatcher'] = $dispatcher;
        }
        parent::__construct($opts);
    }

    /** @return array<string,mixed> */
    private function buildOpts(int $pid): array
    {
        $admission = $this->getActiveAdmission($pid);
        $webroot = OEGlobalsBag::getInstance()->getString('webroot');

        return [
            'acl' => ['patients', 'med'],
            'initiallyCollapsed' => $this->resolveInitiallyCollapsed(),
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

    protected function resolveInitiallyCollapsed(): bool
    {
        $setting = UserSettingsService::getUserSetting(self::CARD_ID . '_expand');
        return ((int)(string)($setting ?? '0')) === 0;
    }

    /** @return array<mixed>|null */
    protected function getActiveAdmission(int $pid): ?array
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
            [$pid, (int)(string)($catRow['pc_catid'] ?? '0')]
        );

        return $row ?: null;
    }
}
