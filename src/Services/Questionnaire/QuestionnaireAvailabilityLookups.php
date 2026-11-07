<?php

/**
 * Pick lists the questionnaire availability screen offers.
 *
 * Facilities, providers, visit categories, questionnaire categories, permissions, the
 * questionnaires registered as encounter forms, and the SMART apps a rule can name. Each
 * comes back already narrowed to plain ids and labels, so the controller and template never
 * handle a raw database row.
 *
 * Not final so the controller's tests can stand in for it without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FHIR\SMART\SmartLaunchController;

class QuestionnaireAvailabilityLookups
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
    ) {
    }

    /**
     * @return array<int, string> facility id => name
     */
    public function facilities(): array
    {
        return $this->idLabelMap(
            QueryUtils::fetchRecordsNoLog('SELECT `id`, `name` FROM `facility` ORDER BY `name`', []),
            'id',
            'name'
        );
    }

    /**
     * @return array<int, string> user id => "Last, First" for active, authorized users
     */
    public function providers(): array
    {
        return $this->idLabelMap(
            QueryUtils::fetchRecordsNoLog(
                "SELECT `id`, CONCAT_WS(', ', `lname`, `fname`) AS `name` FROM `users`
                  WHERE `active` = 1 AND `authorized` = 1 AND `username` <> ''
                  ORDER BY `lname`, `fname`",
                []
            ),
            'id',
            'name'
        );
    }

    /**
     * @return array<int, string> pc_catid => category name, for active calendar categories
     */
    public function visitCategories(): array
    {
        return $this->idLabelMap(
            QueryUtils::fetchRecordsNoLog(
                'SELECT `pc_catid`, `pc_catname` FROM `openemr_postcalendar_categories`
                  WHERE `pc_active` = 1 ORDER BY `pc_seq`, `pc_catname`',
                []
            ),
            'pc_catid',
            'pc_catname'
        );
    }

    /**
     * Questionnaire categories, which are Observation_Types option ids.
     *
     * Titles go through xl_list_label(), which honours the translate_lists global. xl()
     * itself takes literal strings only.
     *
     * @return array<string, string> option id => title
     */
    public function categoryTitles(): array
    {
        $titles = [];
        $rows = QueryUtils::fetchRecordsNoLog(
            "SELECT `option_id`, `title` FROM `list_options`
              WHERE `list_id` = 'Observation_Types' AND `activity` = 1 ORDER BY `seq`, `title`",
            []
        );
        foreach ($rows as $row) {
            $optionId = $row['option_id'] ?? null;
            if (!is_string($optionId) || $optionId === '') {
                continue;
            }
            $title = $row['title'] ?? null;
            $translated = is_string($title) && $title !== '' ? xl_list_label($title) : null;
            $titles[$optionId] = is_string($translated) && $translated !== '' ? $translated : $optionId;
        }

        return $titles;
    }

    /**
     * Permissions a rule can require, as section => ["section|level" => level name].
     *
     * Offered as a list because a free-typed section and level can name a pair the
     * installer never created, which aclCheckCore() denies for everyone. Names are shown as
     * stored, the way the access control admin shows them.
     *
     * @return array<string, array<string, string>>
     */
    public function permissionChoices(): array
    {
        $choices = [];
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT s.`value` AS `section`, s.`name` AS `section_name`,
                    a.`value` AS `level`, a.`name` AS `level_name`
               FROM `gacl_aco` a
              INNER JOIN `gacl_aco_sections` s ON s.`value` = a.`section_value`
              WHERE a.`hidden` = 0 AND s.`hidden` = 0
              ORDER BY s.`order_value`, s.`name`, a.`order_value`, a.`name`',
            []
        );
        foreach ($rows as $row) {
            $section = $row['section'] ?? null;
            $level = $row['level'] ?? null;
            if (!is_string($section) || $section === '' || !is_string($level) || $level === '') {
                continue;
            }
            $sectionName = $row['section_name'] ?? null;
            $levelName = $row['level_name'] ?? null;
            $sectionLabel = is_string($sectionName) && $sectionName !== '' ? $sectionName : $section;
            $choices[$sectionLabel][$section . '|' . $level] = is_string($levelName) && $levelName !== ''
                ? $levelName
                : $level;
        }

        return $choices;
    }

    /**
     * Questionnaires registered as enabled encounter forms.
     *
     * The encounter surface only filters the registered list, so an encounter rule for a
     * questionnaire missing from here has no effect until it is registered.
     *
     * @return array<int, true>
     */
    public function encounterFormIds(): array
    {
        $ids = [];
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT DISTINCT `form_foreign_id` FROM `registry`
              WHERE `directory` = ? AND `form_foreign_id` > 0 AND `state` = 1',
            [QuestionnaireAvailabilityService::ENCOUNTER_FORM_DIRECTORY]
        );
        foreach ($rows as $row) {
            $id = self::positiveInt($row['form_foreign_id'] ?? null);
            if ($id !== null) {
                $ids[$id] = true;
            }
        }

        return $ids;
    }

    /**
     * SMART apps a rule can name: enabled clients registered with the EHR launch scope, the
     * same set Available Assessments offers in its launch menu.
     *
     * @return array<string, string> client id => app name
     */
    public function smartApps(): array
    {
        $apps = [];
        foreach ($this->clientRepository->listClientEntities() as $client) {
            if (!$client->isEnabled() || !$client->hasScope(SmartLaunchController::CLIENT_APP_REQUIRED_LAUNCH_SCOPE)) {
                continue;
            }
            $identifier = self::text($client->getIdentifier());
            if ($identifier === '') {
                continue;
            }
            $name = self::text($client->getName());
            $apps[$identifier] = $name !== '' ? $name : $identifier;
        }

        return $apps;
    }

    /**
     * Rows narrowed to id => label. A row whose id is not a positive integer is dropped
     * rather than coerced, so a malformed key loses its option instead of offering "0".
     *
     * @param list<array<mixed>> $rows
     * @return array<int, string>
     */
    private function idLabelMap(array $rows, string $idColumn, string $labelColumn): array
    {
        $map = [];
        foreach ($rows as $row) {
            $id = self::positiveInt($row[$idColumn] ?? null);
            if ($id === null) {
                continue;
            }
            $map[$id] = self::text($row[$labelColumn] ?? null);
        }

        return $map;
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (is_string($value) && preg_match('/^\d+$/', $value) === 1) {
            $value = (int)$value;
        }

        return is_int($value) && $value > 0 ? $value : null;
    }

    /**
     * The OAuth library's getters are loosely typed; taking them through a mixed parameter
     * narrows them the same way whatever the installed version declares.
     */
    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
