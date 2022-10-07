<?php

/**
 * Service for handling Document templates
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\DocumentTemplates;

use Exception;
use RuntimeException;
use OpenEMR\Services\QuestionnaireService;

/**
 *
 */
class DocumentTemplateService extends QuestionnaireService
{
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? null);
    }

    public function uniqueByKey($source, $key): array
    {
        $i = 0;
        $rtn_array = array();
        $key_array = array();

        foreach ($source as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $rtn_array[$i] = $val;
            }
            $i++;
        }
        return $rtn_array;
    }

    /**
     * Resolve all templates for portal.
     * Also called from getTemplateCategoriesByPids() transaction.
     *
     * @param int    $pid
     * @param string $category
     * @param false  $is_portal
     * @return array
     */
    public function getPortalAssignedTemplates($pid = 0, $category = '', $is_portal = false): array
    {
        // change at your peril! Seriously be careful, lots going on here.
        $bind = array();
        $pid_where = '';
        $cat_where = '';
        $cat_where_add = '';
        if (empty($pid)) {
            $pid_where = 'pid > ?';
            $bind = array(0);
        } else {
            $pid_where = 'pid = ?';
            $bind = array($pid);
        }
        if (!empty($category)) {
            $cat_where = 'Where `category` = ?';
            $cat_where_add = 'And `category` = ?';
            $bind[] = $category;
        }
        $results = [];

        try {
            $sql_patient = "SELECT `pid`, Concat_Ws(', ', `lname`, `fname`) as name FROM `patient_data` WHERE `pid` = ?";
            // get all assigned templates for profiles with patient groups
            $sql = "Select pd.pid, Concat_Ws(', ', `lname`, `fname`) as name, pd.patient_groups, tplId.profile, tplId.member_of, tplId.recurring, tplId.event_trigger, tplId.period, tplId.modified_date as profile_date, tpl.* From `patient_data` pd " .
                "Join `document_template_profiles` as ptd On pd.patient_groups LIKE CONCAT('%',ptd.member_of, '%') And pd.$pid_where " .
                "Join (Select * From `document_template_profiles`) tplId On tplId.profile = ptd.profile And ptd.active = '1' " .
                "Join (Select `id`, `category`, `template_name`, `location`, `template_content`, `mime`, `modified_date` From `document_templates` $cat_where) as tpl On tpl.id = tplId.template_id Group By `pid`, `category`, `template_name` Order By `lname`";
            $query_result = sqlStatement($sql, $bind);
            while ($row = sqlFetchArray($query_result)) {
                if (is_array($row)) {
                    $cat = $row['category'] ?: '';
                    if ($is_portal) {
                        $results[$cat][] = $row;
                    } else {
                        $name = $row['name'];
                        $row['template_content'] = '';
                        $results[$name][$cat][] = $row;
                    }
                }
            }
            // if no templates assigned to any groups
            // then proceed to get any templates sent by either profiles or other.
            if (!$is_portal && !empty($results)) {
                // we have group assigned templates.
                // so we get any templates directly sent to patients
                // then add to group resolved templates.
                foreach ($results as $name => $templates) {
                    $t = array_shift($templates);
                    $bind = array($t[0]['pid']);
                    if (!empty($category)) {
                        $bind[] = $category;
                    }
                    $sql = "SELECT * FROM `document_templates` WHERE `pid` = ? $cat_where_add ORDER BY pid, category";
                    $query_result = sqlStatement($sql, $bind);
                    while ($row = sqlFetchArray($query_result)) {
                        if (is_array($row)) {
                            $cat = $row['category'] ?: '';
                            $row['template_content'] = '';
                            $results[$name][$cat][] = $row;
                        }
                    }
                }
            } else {
                // Because we don't have groups then get any templates directly
                // sent to patients and/or same for portal
                if (!$is_portal) {
                    $sql = "SELECT * FROM `document_templates` WHERE ($pid_where) $cat_where_add ORDER BY pid, category";
                } else {
                    $sql = "SELECT * FROM `document_templates` WHERE (`pid` = '0' Or $pid_where) $cat_where_add ORDER BY pid, category";
                }
                $query_result = sqlStatement($sql, $bind);
                while ($row = sqlFetchArray($query_result)) {
                    if (is_array($row)) {
                        $cat = $row['category'] ?: '';
                        $name = '';
                        if (!empty($row['pid'] && !$is_portal)) {
                            $name = sqlQuery($sql_patient, array($row['pid']))['name'] ?? '';
                        }
                        if ($is_portal) {
                            $results[$cat][] = $row;
                        } else {
                            $cat = $row['category'] ?: '';
                            $row['template_content'] = '';
                            $results[$name][$cat][] = $row;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        return $results;
    }

    /**
     * @param      $pids
     * @param null $category
     * @return array
     */
    public function getTemplateCategoriesByPids($pids, $category = null): array
    {
        $results = array();
        if (empty($pids)) {
            return $results;
        }
        foreach ($pids as $pid) {
            if ($pid <= 0) {
                continue;
            }
            $result = $this->getPortalAssignedTemplates($pid, $category);
            if (empty($result)) {
                continue;
            }
            $results = array_merge_recursive($results, $result);
        }
        return $results;
    }

    /**
     * @return array
     */
    public function fetchAllProfileEvents(): array
    {
        $result = [];
        $events = sqlStatement("SELECT `profile`, `recurring`, `event_trigger`, `period` FROM `document_template_profiles` WHERE `template_id` > '0' GROUP BY `profile`");
        foreach ($events as $event) {
            $result[$event['profile']] = $event;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function fetchProfileEvents($profile): array
    {
        return sqlQuery("SELECT `profile`, `recurring`, `event_trigger`, `period` FROM `document_template_profiles` WHERE `template_id` > '0' AND `profile` = ? GROUP BY `profile` LIMIT 1", array($profile));
    }


    /**
     * @param $template
     * @return mixed int|date
     */
    public function showTemplateFromEvent($template, $show_due_date = false)
    {
        $in_review = false;
        $result = sqlQuery("SELECT * FROM `onsite_documents` WHERE `pid` = ? AND `file_path` = ? ORDER BY `create_date` DESC LIMIT 1", array($template['pid'], $template['id']));
        if (!$result) {
            return true;
        }
        $in_review = $result['denial_reason'] === 'Locked' || $result['denial_reason'] === 'In Review';
        // must be a saved doc pending review so don't show.
        // patient selects the edited doc from their history.
        if (!$in_review) {
            return false;
        }
        if (!isset($template['trigger_event'])) {
            // these are sent templates. Not in group.
            if (!empty($template['profile'])) {
                $event = $this->fetchProfileEvents($template['profile']);
                $template['event_trigger'] = '';
                $template['recurring'] = 1;
                $template['period'] = 0;
                if (is_array($event)) {
                    $template['event_trigger'] = $event['event_trigger'];
                    $template['recurring'] = $event['recurring'];
                    $template['period'] = $event['period'];
                }
            } else {
                return false; // in review or locked so don't show. @todo possibly delete sent template.
            }
        }
        if ($template['event_trigger'] === 'once') {
            return false;
        }
        $period = $template['period'] ?: 0;
        $formatted_future = date('Y-m-d', strtotime($result['create_date'] . "+$period days"));
        $future_date = strtotime($formatted_future);
        $currentDate = strtotime(date('Y-m-d'));
        if ($show_due_date) {
            if (!empty($template['recurring'])) {
                if ($future_date > $currentDate) {
                    return $future_date;
                }
                return 1;
            } else {
                return 0;
            }
        }
        if (!empty($template['recurring'])) {
            if ($future_date > $currentDate) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getPatientsByAllGroups(): array
    {
        $results = [];
        // @TODO limit to portal patients?
        $query_result = sqlStatement(
            'SELECT pid, pubpid, fname, mname, lname, DOB, patient_groups FROM patient_data WHERE patient_groups <> "" ORDER BY `lname`'
        );
        while ($row = sqlFetchArray($query_result)) {
            $groups = explode('|', $row['patient_groups']);
            foreach ($groups as $group) {
                $results[$group][] = $row;
            }
        }
        return $results;
    }

    /**
     * @param $profile
     * @return array
     */
    public function getPatientGroupsByProfile($profile): array
    {
        $rtn = sqlStatement('SELECT `profile`, `member_of`, `active` FROM `document_template_profiles` WHERE `profile` = ? AND `member_of` > ""', array($profile));
        $profile_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $profile_list[$row['profile']][] = $row;
        }
        return $profile_list;
    }

    /**
     * @param $profile
     * @return array
     */
    public function getTemplateListByProfile($profile): array
    {
        $rtn = sqlStatement('SELECT `template_id`, `category` FROM `document_template_profiles` WHERE `profile` = ? AND `template_id` > 0', array($profile));
        $profile_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $profile_list[$row['category']][] = $this->fetchTemplate($row['template_id']);
        }
        return $profile_list;
    }

    /**
     * @param      $id
     * @param null $template_name
     * @return array|false|null
     */
    public function fetchTemplate($id, $template_name = null)
    {
        $return = null;
        if (!empty($id)) {
            $return = sqlQuery('SELECT * FROM `document_templates` WHERE `id` = ?', array($id));
        } elseif (!empty($template_name)) {
            $return = sqlQuery('SELECT * FROM `document_templates` WHERE `template_name` = ?', array($template_name));
        }
        return $return;
    }

    /**
     * @param $profile_groups
     * @return bool
     */
    public function savePatientGroupsByProfile($profile_groups): bool
    {
        sqlStatementNoLog('SET autocommit=0');
        sqlStatementNoLog('START TRANSACTION');

        try {
            sqlQuery('DELETE From `document_template_profiles` WHERE `template_id` = 0');
            $sql = 'INSERT INTO `document_template_profiles` (`id`, `template_id`, `profile`, `template_name`, `category`, `provider`, `modified_date`, `member_of`, `active`) VALUES (NULL, 0, ?, "", "Group", ?, current_timestamp(), ?, ?)';

            foreach ($profile_groups as $profile => $groups) {
                foreach ($groups as $group) {
                    $rtn = sqlInsert($sql, array($profile, $_SESSION['authUserID'] ?? null, $group['group'] ?? '', $group['active']));
                }
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        sqlStatementNoLog('COMMIT');
        sqlStatementNoLog('SET autocommit=1');

        return $rtn ?? 0;
    }

    /**
     * @param $patients
     * @return bool
     */
    public function updateGroupsInPatients($patients): bool
    {
        sqlStatementNoLog('SET autocommit=0');
        sqlStatementNoLog('START TRANSACTION');
        try {
            $rtn = sqlQuery('UPDATE `patient_data` SET `patient_groups` = ? WHERE `pid` > ?', array(null, 0));
            foreach ($patients as $id => $groups) {
                $rtn = sqlQuery('UPDATE `patient_data` SET `patient_groups` = ? WHERE `pid` = ?', array($groups, $id));
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        sqlStatementNoLog('COMMIT');
        sqlStatementNoLog('SET autocommit=1');
        return !$rtn;
    }

    /**
     * @param false $patients_only
     * @return array|\string[][]
     */
    public function fetchPortalAuthUsers($patients_only = false): array
    {
        $response = sqlStatement("SELECT `pid`, `pubpid`, DOB as dob, Concat_Ws(', ', `lname`, `fname`) as ptname FROM `patient_data` WHERE `allow_patient_portal` = 'YES' ORDER BY `lname`");

        $result_data = [];
        if (!$patients_only) {
            $result_data = array(
                ['pid' => '0', 'ptname' => 'All Patients'],
                ['pid' => '-1', 'ptname' => 'Repository'],
            );
        }

        while ($row = sqlFetchArray($response)) {
            $result_data[] = $row;
        }
        return $result_data;
    }

    /**
     * @param int $pid
     * @return array
     */
    public function getTemplateListAllCategories($pid = 0, $exclude_sent = false): array
    {
        $results = array();
        $query_result = sqlStatement('SELECT * FROM `document_templates` WHERE pid = ? ORDER BY `category`', array($pid));
        $exclude = null;
        if ($exclude_sent) {
            $query = sqlStatement('SELECT `template_name` FROM `document_templates` WHERE pid = "0" ORDER BY `category`');
            while ($row = sqlFetchArray($query)) {
                if (is_array($row)) {
                    $exclude[] = $row['template_name'];
                }
            }
        }
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                if ($exclude_sent && in_array($row['template_name'], $exclude)) {
                    continue;
                }
                $results[$row['category'] ?? ''][] = $row;
            }
        }
        return $results;
    }

    /**
     * Reserved to prevent duplicate templates across profiles. TBD.
     *
     * @return array
     */
    public function getTemplateListUnique(): array
    {
        $results = array();
        $sql = "SELECT * FROM `document_templates` " .
            "Where `id` Not In (Select `template_id` From `document_template_profiles` Where `template_id` != '0') And `pid` = '-1'";
        $query_result = sqlStatement($sql);
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                // eliminate templates already in all patients
                $duh = sqlQuery("Select `id` From `document_templates` Where `pid` = '0' And `template_name` = ?", array($row['template_name']))['id'];
                if ($duh) {
                    continue;
                }
                $results[$row['category'] ?? ''][] = $row;
            }
        }
        return $results;
    }

    /**
     * @param null $category
     * @param int  $pid
     * @return array|null[]
     */
    public function getTemplateListByCategory($category = null, $pid = 0): array
    {
        $results = array($category => null);
        $query_result = sqlStatement('SELECT * FROM `document_templates` WHERE category = ? AND pid = ?', array($category, $pid));
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$category][] = $row;
            }
        }
        return $results;
    }

    /**
     * @param null $pid
     * @param null $category
     * @param bool $include_content
     * @return array
     */
    public function getTemplatesByPatient($pid = null, $category = null, $include_content = true): array
    {
        $results = array();
        $bind = array();
        if (empty($pid)) {
            $where = 'WHERE pid > ?';
            $bind = array(0);
        } else {
            $where = 'WHERE pid = ?';
            $bind = array($pid);
        }
        if (!empty($category)) {
            $where .= ' AND category = ?';
            $bind[] = $category;
        }
        $sql = "SELECT * FROM `document_templates` $where ORDER BY location, category";
        $query_result = sqlStatement($sql, $bind);
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                if (!$include_content) {
                    $row['content'] = ''; // not needed in views.
                }
                $results[$row['location']][] = $row;
            }
        }
        return $results;
    }

// can delete

    /**
     * @param null $pid
     * @param null $category
     * @return array
     */
    public function getTemplateCategoriesByPatient($pid = null, $category = null): array
    {
        $results = array();
        $bind = array();
        if (empty($pid)) {
            $where = 'WHERE pid > ?';
            $bind = array($pid ?? 0);
        } else {
            $where = 'WHERE pid = ?';
            $bind = array($pid);
        }
        if (!empty($category)) {
            $where .= ' AND category = ?';
            $bind[] = $category;
        }
        $sql = "SELECT * FROM `document_templates` $where ORDER BY pid, category";
        $query_result = sqlStatement($sql, $bind);
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$row['category']][] = $row;
            }
        }
        return $results;
    }

    /**
     * @param       $template_name
     * @param       $category
     * @param       $file
     * @param array $pids
     * @return int
     * @throws Exception
     */
    public function uploadTemplate($template_name, $category, $file, $pids = [], $q_only = false): int
    {
        $mimetype = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $file);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mimetype = mime_content_type($file);
        } else {
            if (stripos($file, '.pdf') !== false) {
                $mimetype = 'application/pdf';
            }
        }

        $content = file_get_contents($file);

        $id = 0;
        $q_ob = json_decode($content, true); // only pass array
        $is_json = json_last_error() === JSON_ERROR_NONE;
        if ($is_json) {
            if (($q_ob['resourceType'] ?? '') != 'Questionnaire') {
                throw new Exception(xlt("Not a valid Questionnaire resource!"));
            }
            if (empty($category)) {
                $category = 'questionnaire';
            }
            $template_name = $template_name ?: $q_ob['title'] ?? $q_ob['name'];
            $q_id = null;
            $content = "{Questionnaire:$template_name}" . "\n";
            $mimetype = 'application/text';
            $service = new QuestionnaireService();
            $id = $service->saveQuestionnaireResource(json_encode($q_ob), $template_name, null, $q_id, null);
            if (empty($id)) {
                return $id;
            } elseif ($q_only) {
                return $id;
            }
        }
        foreach ($pids as $pid) {
            $id = $this->insertTemplate($pid, $category, $template_name, $content, $mimetype);
        }
        return $id;
    }

    /**
     * @param      $pid
     * @param      $category
     * @param      $template
     * @param      $content
     * @param null $mimetype
     * @param null $profile
     * @return int
     */
    public function insertTemplate($pid, $category, $template, $content, $mimetype = null, $profile = null): int
    {
        // prevent template save if unsafe. Check for escaped and unescaped content.
        if (stripos($content, text('<script')) !== false || stripos($content, '<script') !== false) {
            throw new \RuntimeException(xlt("Template rejected. JavaScript not allowed"));
        }

        $name = null;
        if (!empty($pid)) {
            $name = sqlQuery("SELECT `pid`, Concat_Ws(', ', `lname`, `fname`) as name FROM `patient_data` WHERE `pid` = ?", array($pid))['name'] ?? '';
        } elseif ($pid == -1) {
            $name = 'Repository';
        }

        $sql = "INSERT INTO `document_templates` 
            (`pid`, `provider`,`profile`, `category`, `template_name`, `location`, `status`, `template_content`, `size`, `mime`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE `pid` = ?, `provider`= ?, `template_content`= ?, `size`= ?, `modified_date` = NOW(), `mime` = ?";

        return sqlInsert($sql, array($pid, ($_SESSION['authUserID'] ?? null), ($profile ?: ''), $category ?: '', $template, $name, 'New', $content, strlen($content), $mimetype, $pid, ($_SESSION['authUserID'] ?? null), $content, strlen($content), $mimetype));
    }

    /**
     * @param $profiles
     * @return int
     */
    public function setProfileActiveStatus($profiles): int
    {
        sqlQuery("UPDATE `document_template_profiles` SET `active` = '0' WHERE `template_id` = 0");
        foreach ($profiles as $profile) {
            $rtn = sqlQuery("UPDATE `document_template_profiles` SET `active` = '1' WHERE `profile` = ? AND `template_id` = 0", array($profile));
        }
        return !$rtn;
    }

    /**
     * @param $profile
     * @return int
     */
    public function getProfileActiveStatus($profile)
    {
        $rtn = sqlQuery("Select `active` From `document_template_profiles` WHERE `profile` = ? And `template_id` = 0", array($profile));
        if ($rtn === false) {
            return '0';
        }
        return $rtn['active'] ?: '0';
    }

    /**
     * @param $profiles
     * @return int
     */
    public function sendProfileWithGroups($profiles): int
    {
        $result = 0;
        sqlStatementNoLog('SET autocommit=0');
        sqlStatementNoLog('START TRANSACTION');
        $results = [];
        try {
            foreach ($profiles as $profile) {
                $sql = 'Select pd.pid, ptd.profile, ptd.member_of, tpl.* From `patient_data` pd ' .
                    "Join `document_template_profiles` as ptd On pd.patient_groups LIKE CONCAT('%',ptd.member_of, '%') And ptd.profile = ? " .
                    'Join (Select * From `document_template_profiles`) tplId On tplId.profile = ptd.profile ' .
                    'Join (Select `id`, `category`, `template_name`, `location`, `template_content`, `mime` From `document_templates`) as tpl On tpl.id = tplId.template_id';
                $query_result = sqlStatement($sql, array($profile));
                while ($row = sqlFetchArray($query_result)) {
                    if (is_array($row)) {
                        $tid = $row['template_name'];
                        $result = $this->insertTemplate(
                            $row['pid'],
                            $row['category'],
                            $row['template_name'],
                            $row['template_content'],
                            $row['mime'],
                            $profile
                        );
                        //$results[$row['pid']][$row['profile']][$tid] = $row;
                    }
                }
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        sqlStatementNoLog('COMMIT');
        sqlStatementNoLog('SET autocommit=1');
        return $result;
    }

    /**
     * @param      $pids
     * @param      $templates
     * @param null $category
     * @return int
     */
    public function sendTemplate($pids, $templates, $category = null): int
    {
        $result = 0;
        sqlStatementNoLog('SET autocommit=0');
        sqlStatementNoLog('START TRANSACTION');
        try {
            foreach ($templates as $id => $profile) {
                $template = $this->fetchTemplate($id);
                $destination_category = $template['category'];
                if ($destination_category === 'repository') {
                    $destination_category = $category;
                }
                $content = $template['template_content'];
                $name = $template['template_name'];
                foreach ($pids as $pid) {
                    $result = $this->insertTemplate($pid, $destination_category, $name, $content, $template['mime'], $profile);
                }
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        sqlStatementNoLog('COMMIT');
        sqlStatementNoLog('SET autocommit=1');
        return $result;
    }

    /**
     * @param $id
     * @param $content
     * @return array|false|null
     */
    public function updateTemplateContent($id, $content)
    {
        // prevent template save if unsafe. Check for escaped and unescaped content.
        if (stripos($content, text('<script')) !== false || stripos($content, '<script') !== false) {
            throw new \RuntimeException(xlt("Template rejected. JavaScript not allowed"));
        }

        return sqlQuery('UPDATE `document_templates` SET `template_content` = ?, modified_date = NOW() WHERE `id` = ?', array($content, $id));
    }

    /**
     * @param $id
     * @param $category
     * @return array|false|null
     */
    public function updateTemplateCategory($id, $category)
    {
        return sqlQuery('UPDATE  `document_templates`  SET `category` =  ? WHERE `id` = ?', array($category, $id));
    }

    /**
     * @param      $id
     * @param null $template
     * @return bool
     */
    public function deleteTemplate($id, $template = null): bool
    {
        $profile_delete = false;
        if (!empty($template)) {
            $profile_delete = sqlQuery('DELETE FROM `document_template_profiles` WHERE `template_id` = ?', array($id));
            $delete = sqlQuery('DELETE FROM `document_templates` WHERE `template_name` = ?', array($template));
        } else {
            $delete = sqlQuery('DELETE FROM `document_templates` WHERE `id` = ?', array($id));
        }
        return ($delete && $profile_delete);
    }

    /**
     * @param $profiles_array
     * @return false|int
     */
    public function saveAllProfileTemplates($profiles_array)
    {
        sqlStatementNoLog('SET autocommit=0');
        sqlStatementNoLog('START TRANSACTION');
        try {
            sqlQuery("DELETE FROM `document_template_profiles` WHERE `template_id` > 0");
            $rtn = false;
            foreach ($profiles_array as $profile_array) {
                $form_data = [];
                foreach ($profile_array['form'] as $form) {
                    $form_data[$form['name']] = trim($form['value'] ?? '');
                }
                $rtn = sqlInsert(
                    "INSERT INTO `document_template_profiles` 
            (`template_id`, `profile`, `template_name`, `category`, `provider`, `recurring`, `event_trigger`, `period`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    array($profile_array['id'], $profile_array['profile'],
                        $profile_array['name'], $profile_array['category'], ($_SESSION['authUserID'] ?? null),
                        $form_data['recurring'] ? 1 : 0, $form_data['when'], $form_data['days'])
                );
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        sqlStatementNoLog('COMMIT');
        sqlStatementNoLog('SET autocommit=1');
        return $rtn;
    }

    /**
     * @return array
     */
    public function fetchDefaultGroups(): array
    {
        $rtn = sqlStatement('SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`', array('Patient_Groupings'));
        $category_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $group_list[$row['option_id']] = $row;
        }
        return $group_list;
    }

    /**
     * @return array
     */
    public function fetchDefaultCategories(): array
    {
        $rtn = sqlStatement('SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`', array('Document_Template_Categories'));
        $category_list = array();

        $category_list['General'] = array(
            'option_id' => '',
            'title' => '',
            'seq' => '',
        );
        while ($row = sqlFetchArray($rtn)) {
            $category_list[$row['option_id']] = $row;
        }
        return $category_list;
    }

    /**
     * @return array
     */
    public function fetchDefaultProfiles(): array
    {
        $rtn = sqlStatement('SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`', array('Document_Template_Profiles'));
        $profile_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $profile_list[$row['option_id']] = $row;
        }
        return $profile_list;
    }

    /**
     * @return array
     */
    public function getFormattedCategories(): array
    {
        $rtn = sqlStatement('SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`', array('Document_Template_Categories'));
        $category_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $category_list[$row['option_id']] = $row;
        }
        return $category_list;
    }


    /**
     * @param $pid
     * @param $id
     * @return array|false|null
     */
    public function fetchTemplateStatus($pid, $name)
    {
        $sql = "SELECT `pid`, `create_date`, `doc_type`, `patient_signed_time`, `authorize_signed_time`, `patient_signed_status`, `review_date`, `denial_reason`, `file_name`, `file_path`, `encounter` FROM `onsite_documents` WHERE `pid` = ? AND `file_path` = ? ORDER BY `create_date` DESC LIMIT 1";
        return sqlQuery($sql, array($pid, $name));
    }

    /**
     * @param $profile
     * @return mixed
     */
    public function fetchProfileStatus($profile)
    {
        return sqlQuery('SELECT active FROM `document_template_profiles` WHERE `template_id` = "0" AND `profile` = ?', array($profile))['active'];
    }

    /**
     * @param $token
     * @return array
     */
    public function fetchPatientListByIssuesSearch($token): array
    {
        $result = [];
        $search = '%' . $token . '%';
        $sql = "SELECT pd.pid, pd.`pubpid`, pd.`lname`, pd.`fname`, pd.`mname`, pd.`DOB`, pd.`providerID`, l.title, l.diagnosis FROM `patient_data` pd " .
            "JOIN(SELECT * FROM `lists` WHERE `type` = 'medical_problem' AND (`title` LIKE ? OR `diagnosis` LIKE ?)) AS l ON l.`pid` = pd.`pid` GROUP BY pd.`pid`";
        $rtn = sqlStatement($sql, array($search, $search));
        while ($row = sqlFetchArray($rtn)) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @param $current_patient
     * @param $current_user
     * @return string
     */
    public function renderPortalTemplateMenu($current_patient, $current_user, $dropdown = false): string
    {
        $menu = "";
        $category_list = $this->getFormattedCategories();
        $all_templates = $this->getPortalAssignedTemplates($current_patient, '', true);
        ksort($all_templates);
        foreach ($all_templates as $category => $templates) {
            if (is_array($templates)) {
                $is_category = $category_list[$category]['title'] ?? $category;
                if ($is_category === 'default') {
                    $is_category = '';
                }
                $cat_name = text($is_category);

                $flag = false;
                foreach ($templates as $template) {
                    if ((int)$template['pid'] === 0) {
                        $template['pid'] = $current_patient;
                    }
                    $in_edit = sqlQuery("Select `id`, `doc_type`, `denial_reason` From `onsite_documents` Where (`denial_reason` = 'Editing' Or `denial_reason` = 'In Review') And `pid` = ? And `file_path` = ? Limit 1", array($template['pid'], $template['id'])) ?? 0;
                    if (empty($in_edit)) {
                        $test = $this->showTemplateFromEvent($template);
                        if (!$test) {
                            continue;
                        }
                    }
                    if ($template['template_name'] === 'Help') {
                        continue;
                    }
                    if ((int)$template['pid'] !== 0 && (int)$template['pid'] !== (int)$current_patient) {
                        continue;
                    }
                    if (!$flag) {
                        $flag = true;
                        $menu .= "<div class='h6 text-center'>$cat_name</div>\n";
                    }
                    $id = $template['id'];
                    $btnname = $template['template_name'];
                    if (!empty($in_edit)) {
                        $menu .= '<a class="dropdown-item template-item text-danger btn btn-link" id="' . attr($id) . '"' . ' href="#" onclick="page.editHistoryDocument(' . attr_js($in_edit['id']) . ')">' . text($btnname) . "</a>\n";
                    } else {
                        $menu .= '<a class="dropdown-item template-item text-success btn btn-link" id="' . attr($id) . '"' . ' href="#" onclick="page.newDocument(' . attr_js($current_patient) . ', ' . attr_js($current_user) . ', ' . attr_js($btnname) . ', ' . attr_js($id) . ')">' . text($btnname) . "</a>\n";
                    }
                }
                if (!$flag) {
                    $menu .= "<div class='dropdown-divider'></div>\n";
                }
            }
        }
        return $menu;
    }
}
